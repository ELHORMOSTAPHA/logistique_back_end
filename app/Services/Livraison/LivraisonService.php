<?php

namespace App\Services\Livraison;

use App\Models\Livraison;
use App\Models\LivraisonHistorique;
use App\Models\Stock;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LivraisonService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Livraison>
     */
    public function list(array $query): array|Collection
    {
        $builder = Livraison::query()->with(['stock', 'creator']);

        if (! empty($query['statut'])) {
            $builder->where('statut', $query['statut']);
        }
        if (! empty($query['stock_id'])) {
            $builder->where('stock_id', (int) $query['stock_id']);
        }

        $builder->orderByDesc('created_at');

        $paginated = isset($query['paginated']) && (int) $query['paginated'] === 0
            ? false
            : true;

        if (! $paginated) {
            return $builder->get();
        }

        $perPage = min((int) ($query['per_page'] ?? 15), 100);
        $page    = max(1, (int) ($query['page'] ?? 1));

        return PaginationPayload::fromPaginator(
            $builder->paginate($perPage, ['*'], 'page', $page)
        );
    }

    public function findWithRelations(int $id): ?Livraison
    {
        return Livraison::query()
            ->with(['stock', 'creator', 'livraisonHistoriques.creator'])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId): Livraison
    {
        return DB::transaction(function () use ($data, $userId) {
            $livraison = Livraison::query()->create([
                'stock_id'   => $data['stock_id'],
                'client'     => $data['client'],
                'statut'     => $data['statut'] ?? 'en_attente',
                'ww'         => $data['ww'] ?? null,
                'n_facture'  => $data['n_facture'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            LivraisonHistorique::query()->create([
                'livraison_id' => $livraison->id,
                'statut'       => $livraison->statut,
                'infos'        => null,
                'created_by'   => $userId,
            ]);

            $livraison->load(['stock', 'creator', 'livraisonHistoriques.creator']);

            return $livraison;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data, ?int $userId): ?Livraison
    {
        $livraison = Livraison::query()->find($id);

        if (! $livraison) {
            return null;
        }

        return DB::transaction(function () use ($livraison, $data, $userId) {
            $previousStatut = $livraison->statut;

            $payload = array_filter([
                'client'    => $data['client'] ?? null,
                'statut'    => $data['statut'] ?? null,
                'ww'        => $data['ww'] ?? null,
                'n_facture' => $data['n_facture'] ?? null,
            ], fn($v) => $v !== null);

            $payload['updated_by'] = $userId;
            $livraison->update($payload);

            if (isset($data['statut']) && $data['statut'] !== $previousStatut) {
                LivraisonHistorique::query()->create([
                    'livraison_id' => $livraison->id,
                    'statut'       => $livraison->statut,
                    'infos'        => $data['n_facture'] ?? $data['ww'] ?? null,
                    'created_by'   => $userId,
                ]);
            }

            $livraison->load(['stock', 'creator', 'livraisonHistoriques.creator']);

            return $livraison;
        });
    }

    public function delete(int $id): bool
    {
        $livraison = Livraison::query()->find($id);

        if (! $livraison) {
            return false;
        }

        return (bool) $livraison->delete();
    }

    /**
     * Ajoute une entrée d'historique et met à jour le statut de la livraison.
     *
     * @param  array<string, mixed>  $data
     */
    public function addHistorique(int $livraisonId, array $data, ?int $userId): ?LivraisonHistorique
    {
        $livraison = Livraison::query()->find($livraisonId);

        if (! $livraison) {
            return null;
        }

        return DB::transaction(function () use ($livraison, $data, $userId) {
            $updatePayload = [
                'statut'     => $data['statut'],
                'updated_by' => $userId,
            ];

            if ($data['statut'] === 'facturé' && ! empty($data['n_facture'])) {
                $updatePayload['n_facture'] = $data['n_facture'];
            }

            if ($data['statut'] === 'livré' && ! empty($data['ww'])) {
                $updatePayload['ww'] = $data['ww'];
            }

            $livraison->update($updatePayload);

            $infos = $data['n_facture'] ?? $data['ww'] ?? null;

            $historique = LivraisonHistorique::query()->create([
                'livraison_id' => $livraison->id,
                'statut'       => $data['statut'],
                'infos'        => $infos,
                'created_by'   => $userId,
            ]);

            $historique->load('creator');

            return $historique;
        });
    }

    /**
     * @return Collection<int, LivraisonHistorique>
     */
    public function historiques(int $livraisonId): ?Collection
    {
        if (! Livraison::query()->where('id', $livraisonId)->exists()) {
            return null;
        }

        return LivraisonHistorique::query()
            ->where('livraison_id', $livraisonId)
            ->with('creator')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Crée une livraison depuis une intégration externe (système-à-système).
     *
     * - Cherche le stock par VIN (exact, non supprimé).
     * - Si déjà une livraison active (non-livrée) sur ce stock, retourne celle-ci sans doublon.
     * - Construit le `client` à partir de `nom_client` + `tel_client`.
     * - Le `cmd_id` est enregistré dans le champ `n_facture` (référence commande externe).
     *
     * @param  array<string, mixed>  $data  Keys: vin, nom_client, tel_client, cmd_id
     * @return array{livraison: Livraison, created: bool, stock: Stock|null}
     */
    public function createFromIntegration(array $data): array
    {
        $vin      = trim((string) ($data['vin'] ?? ''));
        $client   = trim(implode(' — ', array_filter([
            trim((string) ($data['nom_client'] ?? '')),
            trim((string) ($data['tel_client'] ?? '')),
        ])));
        $cmdId    = trim((string) ($data['cmd_id'] ?? ''));

        $stock = $vin !== ''
            ? Stock::query()->where('vin', $vin)->first()
            : null;

        if ($stock === null) {
            return ['livraison' => null, 'created' => false, 'stock' => null];
        }

        // Avoid creating a duplicate active livraison for the same stock
        $existing = Livraison::query()
            ->where('stock_id', $stock->id)
            ->whereIn('statut', ['en_attente', 'facturé'])
            ->latest()
            ->first();

        if ($existing) {
            $existing->load(['stock', 'creator', 'livraisonHistoriques.creator']);
            return ['livraison' => $existing, 'created' => false, 'stock' => $stock];
        }

        $livraison = DB::transaction(function () use ($stock, $client, $cmdId) {
            $livraison = Livraison::query()->create([
                'stock_id'   => $stock->id,
                'client'     => $client,
                'telephone'  => $data['tel_client'] ?? null,
                'statut'     => 'en_attente',
                'crm_cmd_id'  => $cmdId !== '' ? $cmdId : null,
                'created_by' => null,
                'updated_by' => null,
            ]);

            LivraisonHistorique::query()->create([
                'livraison_id' => $livraison->id,
                'statut'       => 'en_attente',
                'infos'        => $cmdId !== '' ? 'cmd_id: ' . $cmdId : null,
                'created_by'   => null,
            ]);

            $livraison->load(['stock', 'creator', 'livraisonHistoriques.creator']);

            return $livraison;
        });

        return ['livraison' => $livraison, 'created' => true, 'stock' => $stock];
    }
}
