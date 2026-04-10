<?php

namespace App\Services\DemandeReservation;

use App\Models\DemandeReservation;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;
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

        return $row->fresh()->load(['stock', 'demandeMotifs']);
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
