<?php

namespace App\Services\Depot;

use App\DTOs\Depot\CreateDepotDto;
use App\DTOs\Depot\ListDepotDto;
use App\DTOs\Depot\UpdateDepotDto;
use App\Models\Depot;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;

class DepotService
{
    /**
     * @return array<string, mixed>|Collection<int, Depot>
     */
    public function list(ListDepotDto $dto): array|Collection
    {
        $query = Depot::query();

        if ($dto->name !== null) {
            $query->filterByName($dto->name);
        }
        if ($dto->type !== null) {
            $query->filterByType($dto->type);
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->filterByDate($dto->from, $dto->to);
        }
        if ($dto->created_at !== null) {
            $query->whereDate('created_at', $dto->created_at);
        }
        if ($dto->updated_at !== null) {
            $query->whereDate('updated_at', $dto->updated_at);
        }
        if ($dto->created_by !== null) {
            $query->where('created_by', (string) $dto->created_by);
        }
        if ($dto->deleted_by !== null) {
            $query->where('deleted_by', $dto->deleted_by);
        }
        if ($dto->deleted_at !== null) {
            $query->onlyTrashed()->whereDate('deleted_at', $dto->deleted_at);
        }

        $allowedSort = ['created_at', 'name', 'type', 'id'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateDepotDto $dto, ?int $userId): Depot
    {
        $attributes = $dto->toArray();
        if ($userId !== null) {
            $attributes['created_by'] = (string) $userId;
        }

        return Depot::query()->create($attributes);
    }

    public function findWithRelations(int $id): ?Depot
    {
        return Depot::query()->find($id);
    }

    public function update(int $id, UpdateDepotDto $dto): ?Depot
    {
        $depot = Depot::query()->find($id);

        if (! $depot) {
            return null;
        }

        $data = $dto->toArray();

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
