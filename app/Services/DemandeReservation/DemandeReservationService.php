<?php

namespace App\Services\DemandeReservation;

use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\DemandeReservationResource;
class DemandeReservationService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, DemandeReservation>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::demandeReservation($query);
        $builder = DemandeReservation::query()->with(['stock', 'demandeMotifs']);

        if ($f['stock_id'] !== null) {
            $builder->where('stock_id', $f['stock_id']);
        }
        if ($f['statut'] !== null) {
            $builder->where('statut', 'like', '%'.addcslashes($f['statut'], '%_\\').'%');
        }
        if ($f['id_demande'] !== null) {
            $builder->where('id_demande', 'like', '%'.addcslashes($f['id_demande'], '%_\\').'%');
        }
        if ($f['nom_commercial'] !== null) {
            $builder->where('nom_commercial', 'like', '%'.addcslashes($f['nom_commercial'], '%_\\').'%');
        }
        if ($f['keyword'] !== null) {
            $like = '%'.addcslashes($f['keyword'], '%_\\').'%';
            $builder->where(function ($q) use ($like) {
                $q->where('demande_infos', 'like', $like)
                    ->orWhere('id_demande', 'like', $like)
                    ->orWhere('nom_commercial', 'like', $like);
            });
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->whereBetween('created_at', [$f['from'], $f['to']]);
        }

        $allowedSort = ['created_at', 'id', 'stock_id', 'statut'];
        $sortBy = in_array($f['sort_by'], $allowedSort, true) ? $f['sort_by'] : 'created_at';
        $order = in_array($f['sort_order'], ['asc', 'desc'], true) ? $f['sort_order'] : 'desc';
        $builder->orderBy($sortBy, $order);

        if ($f['paginated'] === false) {
            $rows = $builder->get();
            return $rows;
        }

        $pagination = $builder->paginate($f['per_page'], ['*'], 'page', $f['page'] ?? 1);
        // Ensure nested relations are attached to page items (avoids empty relations after paginate in some setups).
        $pagination->getCollection()->loadMissing(['stock', 'demandeMotifs']);
        $pagination->through(fn (DemandeReservation $row) => (new DemandeReservationResource($row))->resolve());

        return PaginationPayload::fromPaginator($pagination);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): DemandeReservation
    {
        $row = [
            'stock_id' => (int) $data['stock_id'],
            'id_demande' => isset($data['id_demande']) && $data['id_demande'] !== '' ? (string) $data['id_demande'] : null,
            'nom_commercial' => isset($data['nom_commercial']) && $data['nom_commercial'] !== '' ? (string) $data['nom_commercial'] : null,
            'id_commercial' => isset($data['id_commercial']) && $data['id_commercial'] !== '' ? (int) $data['id_commercial'] : null,
            'demande_infos' => isset($data['demande_infos']) && $data['demande_infos'] !== '' ? (string) $data['demande_infos'] : null,
        ];
        if (isset($data['statut']) && $data['statut'] !== '') {
            $row['statut'] = (string) $data['statut'];
        }

        return DemandeReservation::query()->create(array_filter($row, static fn ($v) => $v !== null));
    }

    public function find(int $id): ?DemandeReservation
    {
        return DemandeReservation::query()->with(['stock', 'demandeMotifs'])->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?DemandeReservation
    {
        $row = DemandeReservation::query()->find($id);
        if (! $row) {
            return null;
        }

        $allowed = ['stock_id', 'id_demande', 'nom_commercial', 'id_commercial', 'demande_infos', 'statut'];
        $data = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }
        if ($data !== []) {
            $row->update($data);
        }

        // Notify CRM when demand is accepted
        if (($validated['statut'] ?? null) === 'accepté' && $row->id_demande) {
            $this->syncOrderStatusToCrm((string) $row->id_demande, 'validee');
        }

        return $row->fresh()->load(['stock', 'demandeMotifs']);
    }

    /**
     * Returns available stocks matching the demande vehicle identity, FIFO order.
     * Group 1: stocks with VIN (oldest first).
     * Group 2: arrivage placeholders without VIN (oldest first).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMatchingStock(DemandeReservation $demande): array
    {
        $stock = $demande->stock;
        if (! $stock) {
            return [];
        }

        $modele   = (string) $stock->modele;
        $finition = (string) $stock->finition;
        $colorEx  = (string) $stock->color_ex;
        $colorInt = (string) $stock->color_int;
        $marque   = (string) $stock->marque;

        $baseQuery = fn () => Stock::query()
            ->where('marque',   'like', '%' . $marque   . '%')
            ->where('modele',   'like', '%' . $modele   . '%')
            ->where('finition', 'like', '%' . $finition . '%')
            ->where('color_ex', 'like', '%' . $colorEx  . '%')
            ->where('color_int','like', '%' . $colorInt . '%')
            ->where('reserved', false)
            ->orderBy('created_at', 'asc');

        $toRow = function (Stock $s, bool $inArrivage) {
            $createdAt = Carbon::parse($s->created_at);
            return [
                'id'            => $s->id,
                'vin'           => $s->vin,
                'has_vin'       => ! empty($s->vin),
                'in_arrivage'   => $inArrivage,
                'marque'        => $s->marque,
                'modele'        => $s->modele,
                'finition'      => $s->finition,
                'color_ex'      => $s->color_ex,
                'color_int'     => $s->color_int,
                'stock_age_days'=> (int) $createdAt->diffInDays(now()),
                'created_at'    => $s->created_at,
            ];
        };

        $withVin = $baseQuery()
            ->whereNotNull('vin')->where('vin', '!=', '')
            ->get()->map(fn ($s) => $toRow($s, false));

        $withoutVin = $baseQuery()
            ->where(fn ($q) => $q->whereNull('vin')->orWhere('vin', ''))
            ->get()->map(fn ($s) => $toRow($s, true));

        return $withVin->concat($withoutVin)->values()->all();
    }

    /**
     * Assigns a stock to the demande, marks it reserved, sets statut = 'accepté'.
     *
     * @param  array<string, mixed>  $data
     */
    public function affecterVin(DemandeReservation $demande, array $data): ?DemandeReservation
    {
        $stock = Stock::query()->find((int) $data['stock_id']);
        if (! $stock) {
            return null;
        }

        // Mark stock reserved
        $stock->update(['reserved' => true]);

        // Update demande
        $demande->update([
            'stock_id' => $stock->id,
            'vin'      => $stock->vin ?: null,
            'statut'   => 'accepté',
        ]);

        // Sync to CRM
        if ($demande->id_demande) {
            $this->syncOrderStatusToCrm((string) $demande->id_demande, 'validee');
        }

        return $demande->fresh()->load(['stock', 'demandeMotifs']);
    }

    private function syncOrderStatusToCrm(string $orderId, string $statut): void
    {
        $crmUrl  = rtrim((string) config('app.crm_url'), '/');
        $apiKey  = (string) config('app.crm_api_key');

        if (! $crmUrl || ! $apiKey) {
            Log::warning('[CrmSync] CRM_URL or CRM_API_KEY not configured.');
            return;
        }

        $endpoint = $crmUrl . '/orders/ordersapi/update_order_status';
        $payload  = json_encode(['order_id' => $orderId, 'statut' => $statut]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-Api-Key: ' . $apiKey,
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 8,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode < 200 || $httpCode >= 300) {
            Log::error(sprintf(
                '[CrmSync] Failed to update order #%s in CRM. http=%s curl_err=%s body=%s',
                $orderId, $httpCode, $curlError ?: '-', is_string($response) ? $response : 'null'
            ));
        } else {
            Log::info(sprintf('[CrmSync] Order #%s status set to "%s" in CRM. HTTP %s', $orderId, $statut, $httpCode));
        }
    }

    public function delete(int $id): bool
    {
        $row = DemandeReservation::query()->find($id);
        if (! $row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
