<?php

namespace App\Services\Historique;

use App\Models\Historique;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class HistoriqueService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Historique>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::historique($query);
        $builder = Historique::query();

        if ($f['user_id'] !== null) {
            $builder->where('user_id', $f['user_id']);
        }
        if ($f['action'] !== null) {
            $builder->where('action', 'like', '%'.addcslashes($f['action'], '%_\\').'%');
        }
        if ($f['table_name'] !== null) {
            $builder->where('table_name', 'like', '%'.addcslashes($f['table_name'], '%_\\').'%');
        }
        if ($f['record_id'] !== null) {
            $builder->where('record_id', $f['record_id']);
        }
        if ($f['keyword'] !== null) {
            $like = '%'.addcslashes($f['keyword'], '%_\\').'%';
            $builder->where(function ($q) use ($like) {
                $q->where('old_value', 'like', $like)
                    ->orWhere('new_value', 'like', $like)
                    ->orWhere('table_name', 'like', $like);
            });
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->whereBetween('created_at', [$f['from'], $f['to']]);
        }

        $allowedSort = ['id', 'created_at', 'action', 'table_name'];
        $sortBy = in_array($f['sort_by'], $allowedSort, true) ? $f['sort_by'] : 'id';
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
    public function create(array $data, ?int $userId): Historique
    {
        $attributes = array_filter([
            'user_id' => isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            'action' => isset($data['action']) && $data['action'] !== '' ? (string) $data['action'] : null,
            'table_name' => isset($data['table_name']) && $data['table_name'] !== '' ? (string) $data['table_name'] : null,
            'record_id' => isset($data['record_id']) && $data['record_id'] !== '' ? (int) $data['record_id'] : null,
            'old_value' => isset($data['old_value']) ? (string) $data['old_value'] : null,
            'new_value' => isset($data['new_value']) ? (string) $data['new_value'] : null,
        ], static fn ($v) => $v !== null);
        $attributes['created_by'] = $userId;
        $attributes['created_at'] = Date::now();

        return Historique::query()->create($attributes);
    }

    public function find(int $id): ?Historique
    {
        return Historique::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?Historique
    {
        $row = Historique::query()->find($id);
        if (! $row) {
            return null;
        }

        $data = array_filter([
            'user_id' => $validated['user_id'] ?? null,
            'action' => $validated['action'] ?? null,
            'table_name' => $validated['table_name'] ?? null,
            'record_id' => $validated['record_id'] ?? null,
            'old_value' => $validated['old_value'] ?? null,
            'new_value' => $validated['new_value'] ?? null,
        ], static fn ($v) => $v !== null);
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
