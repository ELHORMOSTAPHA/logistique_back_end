<?php

namespace App\Services\Utilisateur;

use App\DTOs\Utilisateur\CreateUtilisateurDto;
use App\DTOs\Utilisateur\ListUtilisateurDto;
use App\DTOs\Utilisateur\UpdateUtilisateurDto;
use App\Models\User;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UtilisateurService
{
    /**
     * @return array<string, mixed>|Collection<int, User>
     */
    public function list(ListUtilisateurDto $dto): array|Collection
    {
        $query = User::query()->with(['profile']);

        if ($dto->keyword !== null) {
            $kw = '%'.addcslashes($dto->keyword, '%_\\').'%';
            $query->where(function ($q) use ($kw) {
                $q->where('nom', 'like', $kw)
                    ->orWhere('prenom', 'like', $kw)
                    ->orWhere('email', 'like', $kw);
            });
        }
        if ($dto->statut !== null) {
            $query->where('statut', $dto->statut);
        }
        if ($dto->id_profile !== null) {
            $query->where('id_profile', $dto->id_profile);
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->whereBetween('created_at', [$dto->from, $dto->to]);
        }

        $allowedSort = ['created_at', 'id', 'nom', 'prenom', 'email', 'statut'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateUtilisateurDto $dto): User
    {
        $attributes = $dto->toArray();
        $attributes['password'] = Hash::make($dto->password);

        return User::query()->create($attributes);
    }

    public function find(int $id): ?User
    {
        return User::query()->with(['profile'])->find($id);
    }

    public function update(int $id, UpdateUtilisateurDto $dto): ?User
    {
        $user = User::query()->find($id);
        if (! $user) {
            return null;
        }

        $data = $dto->toArray();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
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
