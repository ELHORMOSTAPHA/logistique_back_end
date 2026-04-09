<?php

namespace App\Services\Lot;

use App\DTOs\Lot\CreateLotDto;
use App\DTOs\Lot\ListLotDto;
use App\DTOs\Lot\UpdateLotDto;
use App\Models\Lot;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;

class LotService
{
    /**
     * @return array<string, mixed>|Collection<int, Lot>
     */
    public function list(ListLotDto $dto): array|Collection
    {
        $query = Lot::query();

        if ($dto->numero_lot !== null) {
            $query->where('numero_lot', 'like', '%'.addcslashes($dto->numero_lot, '%_\\').'%');
        }
        if ($dto->numero_arrivage !== null) {
            $query->where('numero_arrivage', 'like', '%'.addcslashes($dto->numero_arrivage, '%_\\').'%');
        }
        if ($dto->statut !== null) {
            $query->where('statut', 'like', '%'.addcslashes($dto->statut, '%_\\').'%');
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->whereBetween('date_arrivage_prevu', [$dto->from, $dto->to]);
        }

        $allowedSort = ['created_at', 'id', 'numero_lot', 'numero_arrivage', 'statut'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateLotDto $dto, ?int $userId): Lot
    {
        $attributes = $dto->toArray();
        if ($userId !== null) {
            $attributes['created_by'] = (string) $userId;
        }

        return Lot::query()->create($attributes);
    }

    public function find(int $id): ?Lot
    {
        return Lot::query()->find($id);
    }

    public function update(int $id, UpdateLotDto $dto, ?int $userId): ?Lot
    {
        $lot = Lot::query()->find($id);
        if (! $lot) {
            return null;
        }

        $data = $dto->toArray();
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
