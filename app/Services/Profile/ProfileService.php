<?php

namespace App\Services\Profile;

use App\DTOs\Profile\CreateProfileDto;
use App\DTOs\Profile\ListProfileDto;
use App\DTOs\Profile\UpdateProfileDto;
use App\Models\Profile;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;

class ProfileService
{
    /**
     * @return array<string, mixed>|Collection<int, Profile>
     */
    public function list(ListProfileDto $dto): array|Collection
    {
        $query = Profile::query();

        if ($dto->nom !== null) {
            $query->where('nom', 'like', '%'.addcslashes($dto->nom, '%_\\').'%');
        }
        if ($dto->libelle !== null) {
            $query->where('libelle', 'like', '%'.addcslashes($dto->libelle, '%_\\').'%');
        }
        if ($dto->statut !== null) {
            $query->where('statut', 'like', '%'.addcslashes($dto->statut, '%_\\').'%');
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->whereBetween('created_at', [$dto->from, $dto->to]);
        }

        $allowedSort = ['created_at', 'id', 'nom', 'libelle', 'statut'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateProfileDto $dto): Profile
    {
        return Profile::query()->create($dto->toArray());
    }

    public function find(int $id): ?Profile
    {
        return Profile::query()->find($id);
    }

    public function update(int $id, UpdateProfileDto $dto): ?Profile
    {
        $profile = Profile::query()->find($id);
        if (! $profile) {
            return null;
        }

        $data = $dto->toArray();
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
