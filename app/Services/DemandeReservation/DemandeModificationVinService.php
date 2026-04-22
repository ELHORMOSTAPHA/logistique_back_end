<?php

namespace App\Services\DemandeReservation;

use App\Models\DemandeModificationVin;
use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Support\PaginationPayload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DemandeModificationVinService
{
    public function __construct(
        private readonly DemandeReservationService $demandeReservationService,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, DemandeModificationVin>
     */
    public function list(array $query): array|Collection
    {
        $paginated = filter_var($query['paginated'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $perPage   = max(1, min(200, (int) ($query['per_page'] ?? 15)));
        $page      = max(1, (int) ($query['page'] ?? 1));

        $allowedSort = ['created_at', 'id', 'statut', 'demandes_reservation_id'];
        $sortBy  = in_array($query['sort_by'] ?? '', $allowedSort, true) ? $query['sort_by'] : 'created_at';
        $order   = in_array($query['sort_order'] ?? '', ['asc', 'desc'], true) ? $query['sort_order'] : 'desc';

        $builder = DemandeModificationVin::query()
            ->with(['demandeReservation', 'stock', 'demandeur', 'valideur']);

        // Default: show only pending requests (dashboard view)
        $statut = $query['statut'] ?? 'en_attente';
        if ($statut !== null && $statut !== '') {
            $builder->where('statut', $statut);
        }

        if (! empty($query['demandes_reservation_id'])) {
            $builder->where('demandes_reservation_id', (int) $query['demandes_reservation_id']);
        }

        if (! empty($query['demandeur_id'])) {
            $builder->where('demandeur_id', (int) $query['demandeur_id']);
        }

        $builder->orderBy($sortBy, $order);

        if ($paginated === false) {
            return $builder->get();
        }

        $pagination = $builder->paginate($perPage, ['*'], 'page', $page);

        return PaginationPayload::fromPaginator($pagination);
    }

    /**
     * Create a new VIN modification request.
     * Captures the current VIN (vin_initial) from the demande and the new VIN from the target stock.
     *
     * @param  array<string, mixed>  $data
     * @throws \InvalidArgumentException if no VIN is currently assigned or target stock has no VIN
     */
    public function create(array $data, ?int $demandeurId): DemandeModificationVin
    {
        $demande    = DemandeReservation::query()->with('stock')->find((int) $data['demandes_reservation_id']);
        $stock      = Stock::query()->find((int) $data['stock_id']);
        $vinInitial = $demande?->vin ?? $demande?->stock?->vin ?? null;

        if (! $demande || empty($vinInitial)) {
            throw new \InvalidArgumentException('Aucun VIN n\'est actuellement affecté à cette demande.');
        }

        if (! $stock || empty($stock->vin)) {
            throw new \InvalidArgumentException('Le stock cible ne possède pas de VIN.');
        }

        return DemandeModificationVin::query()->create([
            'demandes_reservation_id' => $demande->id,
            'stock_id'                => $stock->id,
            'demandeur_id'            => $demandeurId ?? Auth::id(),
            'vin_initial'             => (string) $vinInitial,
            'vin_nouveau'             => (string) $stock->vin,
            'motif'                   => (string) $data['motif'],
            'statut'                  => 'en_attente',
        ]);
    }

    public function find(int $id): ?DemandeModificationVin
    {
        return DemandeModificationVin::query()
            ->with(['demandeReservation', 'stock', 'demandeur', 'valideur'])
            ->find($id);
    }

    /**
     * Admin approves: triggers the actual VIN modification on the demande.
     */
    public function approuver(DemandeModificationVin $dmv, ?int $valideurId): ?DemandeModificationVin
    {
        if ($dmv->statut !== 'en_attente') {
            return null;
        }

        $demande = $dmv->demandeReservation()->first();
        if (! $demande) {
            return null;
        }

        // Trigger the actual VIN change via DemandeReservationService
        $updated = $this->demandeReservationService->modifierVin($demande, [
            'stock_id' => $dmv->stock_id,
        ]);

        if (! $updated) {
            return null;
        }

        $dmv->update([
            'statut'       => 'approuvée',
            'valideur_id'  => $valideurId,
            'validated_at' => Carbon::now(),
        ]);

        return $dmv->fresh()->load(['demandeReservation', 'stock', 'demandeur', 'valideur']);
    }

    /**
     * Admin rejects the VIN modification request.
     */
    public function refuser(DemandeModificationVin $dmv, ?int $valideurId, ?string $motifRefus): ?DemandeModificationVin
    {
        if ($dmv->statut !== 'en_attente') {
            return null;
        }

        $dmv->update([
            'statut'       => 'refusée',
            'valideur_id'  => $valideurId,
            'validated_at' => Carbon::now(),
            'motif_refus'  => $motifRefus,
        ]);

        return $dmv->fresh()->load(['demandeReservation', 'stock', 'demandeur', 'valideur']);
    }

    public function delete(int $id): bool
    {
        $row = DemandeModificationVin::query()->find($id);
        if (! $row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
