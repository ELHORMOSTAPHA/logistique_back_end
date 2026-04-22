<?php

namespace App\Services\Livraison;

use App\Models\Livraison;
use App\Models\LivraisonHistorique;
use App\Support\PaginationPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LivraisonService
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, Livraison>
     */
    public function list(array $query): array|Collection
    {
        $builder = Livraison::query()->with(['stock', 'creator']);

        if (! empty($query['statut'])) {
            $builder->where('statut', $query['statut']);
        }
        if (! empty($query['stock_id'])) {
            $builder->where('stock_id', (int) $query['stock_id']);
        }

        $builder->orderByDesc('created_at');

        $paginated = isset($query['paginated']) && (int) $query['paginated'] === 0
            ? false
            : true;

        if (! $paginated) {
            return $builder->get();
        }

        $perPage = min((int) ($query['per_page'] ?? 15), 100);
        $page    = max(1, (int) ($query['page'] ?? 1));

        return PaginationPayload::fromPaginator(
            $builder->paginate($perPage, ['*'], 'page', $page)
        );
    }

    public function findWithRelations(int $id): ?Livraison
    {
        return Livraison::query()
            ->with(['stock', 'creator', 'livraisonHistoriques.creator'])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId): Livraison
    {
        return DB::transaction(function () use ($data, $userId) {
            $livraison = Livraison::query()->create([
                'stock_id'   => $data['stock_id'],
                'client'     => $data['client'],
                'statut'     => $data['statut'] ?? 'en_attente',
                'ww'         => $data['ww'] ?? null,
                'n_facture'  => $data['n_facture'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            LivraisonHistorique::query()->create([
                'livraison_id' => $livraison->id,
                'statut'       => $livraison->statut,
                'infos'        => null,
                'created_by'   => $userId,
            ]);

            $livraison->load(['stock', 'creator', 'livraisonHistoriques.creator']);

            return $livraison;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data, ?int $userId): ?Livraison
    {
        $livraison = Livraison::query()->find($id);

        if (! $livraison) {
            return null;
        }

        return DB::transaction(function () use ($livraison, $data, $userId) {
            $previousStatut = $livraison->statut;

            $payload = array_filter([
                'client'    => $data['client'] ?? null,
                'statut'    => $data['statut'] ?? null,
                'ww'        => $data['ww'] ?? null,
                'n_facture' => $data['n_facture'] ?? null,
            ], fn($v) => $v !== null);

            $payload['updated_by'] = $userId;
            $livraison->update($payload);

            if (isset($data['statut']) && $data['statut'] !== $previousStatut) {
                LivraisonHistorique::query()->create([
                    'livraison_id' => $livraison->id,
                    'statut'       => $livraison->statut,
                    'infos'        => $data['n_facture'] ?? $data['ww'] ?? null,
                    'created_by'   => $userId,
                ]);
            }

            $livraison->load(['stock', 'creator', 'livraisonHistoriques.creator']);

            return $livraison;
        });
    }

    public function delete(int $id): bool
    {
        $livraison = Livraison::query()->find($id);

        if (! $livraison) {
            return false;
        }

        return (bool) $livraison->delete();
    }

    /**
     * Ajoute une entrée d'historique et met à jour le statut de la livraison.
     *
     * @param  array<string, mixed>  $data
     */
    public function addHistorique(int $livraisonId, array $data, ?int $userId): ?LivraisonHistorique
    {
        $livraison = Livraison::query()->find($livraisonId);

        if (! $livraison) {
            return null;
        }

        return DB::transaction(function () use ($livraison, $data, $userId) {
            $livraison->update([
                'statut'     => $data['statut'],
                'updated_by' => $userId,
            ]);

            $historique = LivraisonHistorique::query()->create([
                'livraison_id' => $livraison->id,
                'statut'       => $data['statut'],
                'infos'        => $data['infos'] ?? null,
                'created_by'   => $userId,
            ]);

            $historique->load('creator');

            return $historique;
        });
    }

    /**
     * @return Collection<int, LivraisonHistorique>
     */
    public function historiques(int $livraisonId): ?Collection
    {
        if (! Livraison::query()->where('id', $livraisonId)->exists()) {
            return null;
        }

        return LivraisonHistorique::query()
            ->where('livraison_id', $livraisonId)
            ->with('creator')
            ->orderByDesc('created_at')
            ->get();
    }
}
