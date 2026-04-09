<?php

namespace App\Services\Stock;

use App\DTOs\Stock\ChangeDepotDto;
use App\DTOs\Stock\CreateStockDto;
use App\DTOs\Stock\ListStockDto;
use App\DTOs\Stock\UpdateStockDto;
use App\Models\Stock;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;

class StockService
{
    /**
     * @return array<string, mixed>|Collection<int, Stock>
     */
    public function list(ListStockDto $dto): array|Collection
    {
        $query = Stock::query()->with(['depot','stockStatus']);

        if ($dto->name !== null) {
            $query->filterByName($dto->name);
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->filterByDate($dto->from, $dto->to);
        }
        if ($dto->marque !== null) {
            $query->filterByMarque($dto->marque);
        }
        if ($dto->modele !== null) {
            $query->filterByModele($dto->modele);
        }
        if ($dto->vin !== null) {
            $query->where('vin', $dto->vin);
        }
        if ($dto->reserved !== null) {
            $query->filterByReserved($dto->reserved);
        }
        if ($dto->depot_id !== null) {
            $query->filterByDepotId($dto->depot_id);
        }
        if ($dto->lot_id !== null) {
            $query->filterByLotId($dto->lot_id);
        }

        $allowedSort = ['created_at', 'modele', 'marque', 'vin', 'id'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateStockDto $dto, ?int $userId): Stock
    {
        try {
            $attributes = $dto->toArray();
            $attributes['created_by'] = $userId;
            $stock = Stock::query()->create($attributes);
            $stock->load(['depot', 'lot']);
            return $stock;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
    public function findWithRelations(int $id): ?Stock
    {
        try {
            return Stock::query()->with(['depot', 'lot'])->find($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(int $id, UpdateStockDto $dto, ?int $userId): ?Stock
    {
        try {
            $stock = Stock::findOrFail($id);
            if (! $stock) {
                $this->error(MessageKey::NOT_FOUND, 'Stock not found');
            }
            $stock->update($dto->toArray());
            return $stock;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        $stock = Stock::query()->find($id);

        if (! $stock) {
            return false;
        }

        return (bool) $stock->delete();
    }

    public function changeDepot(int $id, ChangeDepotDto $dto, ?int $userId): ?Stock
    {
        $stock = Stock::query()->find($id);

        if (! $stock) {
            return null;
        }

        $stock->update([
            'depot_id' => $dto->depot_id,
            'updated_by' => $userId,
        ]);
        $stock->load(['depot', 'lot']);

        return $stock;
    }
    public function importStock(ImportStockRequest $request)
    {

    }
}
