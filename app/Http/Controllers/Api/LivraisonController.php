<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Livraison\StoreLivraisonHistoriqueRequest;
use App\Http\Requests\Livraison\StoreLivraisonRequest;
use App\Http\Requests\Livraison\UpdateLivraisonRequest;
use App\Http\Resources\Livraison\LivraisonHistoriqueResource;
use App\Http\Resources\Livraison\LivraisonResource;
use App\Services\Livraison\LivraisonService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivraisonController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly LivraisonService $livraisonService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->livraisonService->list($request->query());
            return $this->success($result, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreLivraisonRequest $request): JsonResponse
    {
        try {
            $livraison = $this->livraisonService->create($request->validated(), Auth::id());
            return $this->success(new LivraisonResource($livraison), MessageKey::CREATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $livraison = $this->livraisonService->findWithRelations($id);
            if (! $livraison) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success(new LivraisonResource($livraison), MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function update(UpdateLivraisonRequest $request, int $id): JsonResponse
    {
        try {
            $livraison = $this->livraisonService->update($id, $request->validated(), Auth::id());
            if (! $livraison) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success(new LivraisonResource($livraison), MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            if (! $this->livraisonService->delete($id)) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success(null, MessageKey::DELETED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function addHistorique(StoreLivraisonHistoriqueRequest $request, int $id): JsonResponse
    {
        try {
            $historique = $this->livraisonService->addHistorique($id, $request->validated(), Auth::id());
            if (! $historique) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success(new LivraisonHistoriqueResource($historique), MessageKey::CREATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function historiques(int $id): JsonResponse
    {
        try {
            $historiques = $this->livraisonService->historiques($id);
            if ($historiques === null) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success(LivraisonHistoriqueResource::collection($historiques), MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }
}
