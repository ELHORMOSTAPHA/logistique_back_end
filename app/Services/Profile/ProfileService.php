<?php

namespace App\Services\Profile;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Profile;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * @param  array<string, mixed>  $data
     */
    public function bulkUpdateStatut(array $data): int
    {
        $statut = (string) ($data['statut'] ?? '');
        if (! in_array($statut, ['actif', 'inactif'], true)) {
            throw new \InvalidArgumentException('Invalid statut');
        }

        if (! empty($data['select_all'])) {
            $f = QueryFilterNormalizer::profile($data['filters'] ?? []);
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
            if (! empty($data['excluded_ids'])) {
                $builder->whereNotIn('id', array_map('intval', $data['excluded_ids']));
            }

            return $builder->update(['statut' => $statut]);
        }

        $ids = array_map('intval', $data['ids'] ?? []);
        if ($ids === []) {
            return 0;
        }

        return Profile::query()->whereIn('id', $ids)->update(['statut' => $statut]);
    }

    /**
     * All modules with current permission flags for a profile (missing rows default to false).
     *
     * @return array{profile: Profile, rows: array<int, array<string, mixed>>}
     */
    public function permissionMatrix(int $profileId): array
    {
        $profile = Profile::query()->findOrFail($profileId);
        $modules = Module::query()->orderBy('position')->orderBy('id')->get();
        $byModule = Permission::query()
            ->where('profile_id', $profileId)
            ->get()
            ->keyBy('module_id');

        $rows = $modules->map(function (Module $m) use ($byModule) {
            $p = $byModule->get($m->id);

            return [
                'module_id' => $m->id,
                'name' => $m->name,
                'label' => $m->label,
                'url' => $m->url,
                'position' => $m->position,
                'can_read' => $p ? (bool) $p->can_read : false,
                'can_create' => $p ? (bool) $p->can_create : false,
                'can_update' => $p ? (bool) $p->can_update : false,
                'can_delete' => $p ? (bool) $p->can_delete : false,
            ];
        })->values()->all();

        return ['profile' => $profile, 'rows' => $rows];
    }

    /**
     * @param  array<int, array{module_id: int, can_read?: bool, can_create?: bool, can_update?: bool, can_delete?: bool}>  $permissionRows
     */
    public function syncPermissions(int $profileId, array $permissionRows): void
    {
        Profile::query()->findOrFail($profileId);

        DB::transaction(function () use ($profileId, $permissionRows) {
            foreach ($permissionRows as $row) {
                $moduleId = (int) ($row['module_id'] ?? 0);
                if ($moduleId < 1) {
                    continue;
                }

                Permission::query()->updateOrCreate(
                    [
                        'profile_id' => $profileId,
                        'module_id' => $moduleId,
                    ],
                    [
                        'can_read' => (bool) ($row['can_read'] ?? false),
                        'can_create' => (bool) ($row['can_create'] ?? false),
                        'can_update' => (bool) ($row['can_update'] ?? false),
                        'can_delete' => (bool) ($row['can_delete'] ?? false),
                    ]
                );
            }
        });
    }
}
