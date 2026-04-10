<?php

namespace App\Services\Utilisateur;

use App\Models\User;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UtilisateurService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, User>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::utilisateur($query);
        $builder = User::query()->with(['profile']);

        if ($f['keyword'] !== null) {
            $kw = '%'.addcslashes($f['keyword'], '%_\\').'%';
            $builder->where(function ($q) use ($kw) {
                $q->where('nom', 'like', $kw)
                    ->orWhere('prenom', 'like', $kw)
                    ->orWhere('email', 'like', $kw);
            });
        }
        if ($f['statut'] !== null) {
            $builder->where('statut', $f['statut']);
        }
        if ($f['id_profile'] !== null) {
            $builder->where('id_profile', $f['id_profile']);
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $builder->whereBetween('created_at', [$f['from'], $f['to']]);
        }

        $allowedSort = ['created_at', 'id', 'nom', 'prenom', 'email', 'statut'];
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
    public function create(array $data): User
    {
        $attributes = array_filter([
            'nom' => (string) $data['nom'],
            'prenom' => (string) $data['prenom'],
            'email' => (string) $data['email'],
            'telephone' => isset($data['telephone']) && $data['telephone'] !== '' ? (string) $data['telephone'] : null,
            'id_profile' => isset($data['id_profile']) && $data['id_profile'] !== '' ? (int) $data['id_profile'] : null,
            'statut' => isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : 'actif',
            'avatar' => isset($data['avatar']) && $data['avatar'] !== '' ? (string) $data['avatar'] : null,
        ], static fn ($v) => $v !== null);
        $attributes['password'] = Hash::make((string) $data['password']);

        return User::query()->create($attributes);
    }

    public function find(int $id): ?User
    {
        return User::query()->with(['profile'])->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?User
    {
        $user = User::query()->find($id);
        if (! $user) {
            return null;
        }

        $data = array_filter([
            'nom' => $validated['nom'] ?? null,
            'prenom' => $validated['prenom'] ?? null,
            'email' => $validated['email'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'id_profile' => $validated['id_profile'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'avatar' => $validated['avatar'] ?? null,
        ], static fn ($v) => $v !== null);
        if (array_key_exists('password', $validated) && $validated['password'] !== null && $validated['password'] !== '') {
            $data['password'] = Hash::make((string) $validated['password']);
        }

        if ($data !== []) {
            $user->update($data);
        }

        return $user->fresh()->load(['profile']);
    }

    public function delete(int $id): bool
    {
        $user = User::query()->find($id);
        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }
}
