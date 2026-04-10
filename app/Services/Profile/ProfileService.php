<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;

class ProfileService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Profile>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::profile($query);
        $builder = Profile::query();

        if ($f['nom'] !== null) {
            $builder->where('nom', 'like', '%'.addcslashes($f['nom'], '%_\\').'%');
        }
        if ($f['libelle'] !== null) {
            $builder->where('libelle', 'like', '%'.addcslashes($f['libelle'], '%_\\').'%');
        }
        if ($f['statut'] !== null) {
            $builder->where('statut', 'like', '%'.addcslashes($f['statut'], '%_\\').'%');
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->whereBetween('created_at', [$f['from'], $f['to']]);
        }

        $allowedSort = ['created_at', 'id', 'nom', 'libelle', 'statut'];
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
    public function create(array $data): Profile
    {
        $attributes = array_filter([
            'nom' => (string) $data['nom'],
            'libelle' => isset($data['libelle']) && $data['libelle'] !== '' ? (string) $data['libelle'] : null,
            'statut' => isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : null,
        ], static fn ($v) => $v !== null);
        if (! isset($attributes['statut'])) {
            $attributes['statut'] = 'actif';
        }

        return Profile::query()->create($attributes);
    }

    public function find(int $id): ?Profile
    {
        return Profile::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?Profile
    {
        $profile = Profile::query()->find($id);
        if (! $profile) {
            return null;
        }

        $data = array_filter([
            'nom' => $validated['nom'] ?? null,
            'libelle' => $validated['libelle'] ?? null,
            'statut' => $validated['statut'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');
        if ($data !== []) {
            $profile->update($data);
        }

        return $profile->fresh();
    }

    public function delete(int $id): bool
    {
        $profile = Profile::query()->find($id);
        if (! $profile) {
            return false;
        }

        return (bool) $profile->delete();
    }
}
