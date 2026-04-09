<?php

namespace App\Services\DemandeReservation;

use App\DTOs\DemandeReservation\CreateDemandeReservationDto;
use App\DTOs\DemandeReservation\ListDemandeReservationDto;
use App\DTOs\DemandeReservation\UpdateDemandeReservationDto;
use App\Models\DemandeReservation;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;

class DemandeReservationService
{
    /**
     * @return array<string, mixed>|Collection<int, DemandeReservation>
     */
    public function list(ListDemandeReservationDto $dto): array|Collection
    {
        $query = DemandeReservation::query()->with(['stock']);

        if ($dto->stock_id !== null) {
            $query->where('stock_id', $dto->stock_id);
        }
        if ($dto->statut !== null) {
            $query->where('statut', 'like', '%'.addcslashes($dto->statut, '%_\\').'%');
        }
        if ($dto->id_demande !== null) {
            $query->where('id_demande', 'like', '%'.addcslashes($dto->id_demande, '%_\\').'%');
        }
        if ($dto->nom_commercial !== null) {
            $query->where('nom_commercial', 'like', '%'.addcslashes($dto->nom_commercial, '%_\\').'%');
        }
        if ($dto->keyword !== null) {
            $like = '%'.addcslashes($dto->keyword, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('demande_infos', 'like', $like)
                    ->orWhere('id_demande', 'like', $like)
                    ->orWhere('nom_commercial', 'like', $like);
            });
        }
        if ($dto->from !== null && $dto->to !== null) {
            $query->whereBetween('created_at', [$dto->from, $dto->to]);
        }

        $allowedSort = ['created_at', 'id', 'stock_id', 'statut'];
        $sortBy = in_array($dto->sort_by, $allowedSort, true) ? $dto->sort_by : 'created_at';
        $order = in_array($dto->sort_order, ['asc', 'desc'], true) ? $dto->sort_order : 'desc';
        $query->orderBy($sortBy, $order);

        if ($dto->paginated === false) {
            return $query->get();
        }

        $pagination = $query->paginate($dto->per_page, ['*'], 'page', $dto->page ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    public function create(CreateDemandeReservationDto $dto): DemandeReservation
    {
        return DemandeReservation::query()->create($dto->toArray());
    }

    public function find(int $id): ?DemandeReservation
    {
        return DemandeReservation::query()->with(['stock'])->find($id);
    }

    public function update(int $id, UpdateDemandeReservationDto $dto): ?DemandeReservation
    {
        $row = DemandeReservation::query()->find($id);
        if (! $row) {
            return null;
        }

        $data = $dto->toArray();
        if ($data !== []) {
            $row->update($data);
        }

        return $row->fresh()->load(['stock']);
    }

    public function delete(int $id): bool
    {
        $row = DemandeReservation::query()->find($id);
        if (! $row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
