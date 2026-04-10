<?php

namespace App\Services\Depot;

use App\Models\Depot;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;

class DepotService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Depot>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::depot($query);
        $builder = Depot::query();

        if ($f['name'] !== null) {
            $builder->filterByName($f['name']);
        }
        if ($f['type'] !== null) {
            $builder->filterByType($f['type']);
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->filterByDate($f['from'], $f['to']);
        }
        if ($f['created_at'] !== null) {
            $builder->whereDate('created_at', $f['created_at']);
        }
        if ($f['updated_at'] !== null) {
            $builder->whereDate('updated_at', $f['updated_at']);
        }
        if ($f['created_by'] !== null) {
            $builder->where('created_by', (string) $f['created_by']);
        }
        if ($f['deleted_by'] !== null) {
            $builder->where('deleted_by', $f['deleted_by']);
        }
        if ($f['deleted_at'] !== null) {
            $builder->onlyTrashed()->whereDate('deleted_at', $f['deleted_at']);
        }

        $allowedSort = ['created_at', 'name', 'type', 'id'];
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
    public function create(array $data, ?int $userId): Depot
    {
        $attributes = array_filter([
            'name' => $data['name'] ?? null,
            'type' => $data['type'] ?? null,
        ], static fn ($v) => $v !== null);
        if ($userId !== null) {
            $attributes['created_by'] = (string) $userId;
        }

        return Depot::query()->create($attributes);
    }

    public function findWithRelations(int $id): ?Depot
    {
        return Depot::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?Depot
    {
        $depot = Depot::query()->find($id);

        if (! $depot) {
            return null;
        }

        $data = array_filter([
            'name' => $validated['name'] ?? null,
            'type' => $validated['type'] ?? null,
        ], static fn ($v) => $v !== null);

        if ($data !== []) {
            $depot->update($data);
        }

        return $depot->fresh();
    }

    public function delete(int $id): bool
    {
        $depot = Depot::query()->find($id);

        if (! $depot) {
            return false;
        }

        return (bool) $depot->delete();
    }
}
