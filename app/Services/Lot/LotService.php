<?php

namespace App\Services\Lot;

use App\Models\Lot;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;

class LotService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Lot>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::lot($query);
        $builder = Lot::query();

        if ($f['numero_lot'] !== null) {
            $builder->where('numero_lot', 'like', '%'.addcslashes($f['numero_lot'], '%_\\').'%');
        }
        if ($f['numero_arrivage'] !== null) {
            $builder->where('numero_arrivage', 'like', '%'.addcslashes($f['numero_arrivage'], '%_\\').'%');
        }
        if ($f['statut'] !== null) {
            $builder->where('statut', 'like', '%'.addcslashes($f['statut'], '%_\\').'%');
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->whereBetween('date_arrivage_prevu', [$f['from'], $f['to']]);
        }

        $allowedSort = ['created_at', 'id', 'numero_lot', 'numero_arrivage', 'statut'];
        $sortBy = in_array($f['sort_by'], $allowedSort, true) ? $f['sort_by'] : 'created_at';
        $order = in_array($f['sort_order'], ['asc', 'desc'], true) ? $f['sort_order'] : 'desc';
        $builder->orderBy($sortBy, $order);

        if ($f['paginated'] === false) {
            return $builder->get();
        }

        $pagination = $builder->paginate($f['per_page'], ['*'], 'page', $f['page'] ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId): Lot
    {
        $attributes = array_filter([
            'numero_lot' => isset($data['numero_lot']) && $data['numero_lot'] !== '' ? (string) $data['numero_lot'] : null,
            'numero_arrivage' => isset($data['numero_arrivage']) && $data['numero_arrivage'] !== '' ? (string) $data['numero_arrivage'] : null,
            'statut' => isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : null,
            'date_arrivage_prevu' => isset($data['date_arrivage_prevu']) && $data['date_arrivage_prevu'] !== '' ? (string) $data['date_arrivage_prevu'] : null,
        ], static fn ($v) => $v !== null);
        if ($userId !== null) {
            $attributes['created_by'] = (string) $userId;
        }

        return Lot::query()->create($attributes);
    }

    public function find(int $id): ?Lot
    {
        return Lot::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated, ?int $userId): ?Lot
    {
        $lot = Lot::query()->find($id);
        if (! $lot) {
            return null;
        }

        $data = array_filter([
            'numero_lot' => $validated['numero_lot'] ?? null,
            'numero_arrivage' => $validated['numero_arrivage'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'date_arrivage_prevu' => $validated['date_arrivage_prevu'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');
        if ($userId !== null) {
            $data['updated_by'] = $userId;
        }

        if ($data !== []) {
            $lot->update($data);
        }

        return $lot->fresh();
    }

    public function delete(int $id): bool
    {
        $lot = Lot::query()->find($id);
        if (! $lot) {
            return false;
        }

        return (bool) $lot->delete();
    }
}
