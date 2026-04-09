<?php

namespace App\Services\Historique;

use App\DTOs\Historique\CreateHistoriqueDto;
use App\DTOs\Historique\ListHistoriqueDto;
use App\DTOs\Historique\UpdateHistoriqueDto;
use App\Models\Historique;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class HistoriqueService
{
    /**
     * @return array<string, mixed>|Collection<int, Historique>
     */
    public function list(ListHistoriqueDto $dto): array|Collection
    {
        $query = Historique::query();

        if ($dto->user_id !== null) {
            $query->where('user_id', $dto->user_id);
        }
        if ($dto->action !== null) {
            $query->where('action', 'like', '%'.addcslashes($dto->action, '%_\\').'%');
        }
        if ($dto->table_name !== null) {
            $query->where('table_name', 'like', '%'.addcslashes($dto->table_name, '%_\\').'%');
        }
        if ($dto->record_id !== null) {
            $query->where('record_id', $dto->record_id);
        }
        if ($dto->keyword !== null) {
            $like = '%'.addcslashes($dto->keyword, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('old_value', 'like', $like)
                    ->orWhere('new_value', 'like', $like)
                    ->orWhere('table_name', 'like', $like);
            });
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->whereBetween('created_at', [$dto->from, $dto->to]);
        }

        $allowedSort = ['id', 'created_at', 'action', 'table_name'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'id';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateHistoriqueDto $dto, ?int $userId): Historique
    {
        $attributes = $dto->toArray();
        $attributes['created_by'] = $userId;
        $attributes['created_at'] = Date::now();

        return Historique::query()->create($attributes);
    }

    public function find(int $id): ?Historique
    {
        return Historique::query()->find($id);
    }

    public function update(int $id, UpdateHistoriqueDto $dto): ?Historique
    {
        $row = Historique::query()->find($id);
        if (! $row) {
            return null;
        }

        $data = $dto->toArray();
        if ($data !== []) {
            $row->update($data);
        }

        return $row->fresh();
    }

    public function delete(int $id): bool
    {
        $row = Historique::query()->find($id);
        if (! $row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
