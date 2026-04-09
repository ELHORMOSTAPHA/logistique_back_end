<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\ChangeDepotStockRequest;
use App\Http\Requests\Stock\IndexStockRequest;
use App\Http\Requests\Stock\StoreStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Enums\MessageKey;
use App\Services\Stock\StockService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\DTOs\Stock\UpdateStockDto;

class StockController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Liste tous les véhicules en stock.
     * GET /api/stocks
     */
    public function index(IndexStockRequest $request): JsonResponse
    {
        try {
        $stocks = $this->stockService->list($request->toFilterDto());
            return $this->success($stocks, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    /**
     * Créer un nouveau véhicule en stock.
     * POST /api/stocks
     */
    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            $stock = $this->stockService->create($request->toDto(), Auth::id());
            return $this->success($stock, MessageKey::CREATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    /**
     * Afficher un véhicule en stock.
     * GET /api/stocks/{id}
     */
    public function show(int $id): JsonResponse
    {
        $stock = $this->stockService->findWithRelations($id);

        if (! $stock) {
            return response()->json(['message' => 'Véhicule introuvable.'], 404);
        }

        return response()->json($stock);
    }

    /**
     * Mettre à jour un véhicule en stock.
     * PUT /api/stocks/{id}
     */
    public function update(UpdateStockRequest $request, int $id): JsonResponse
    {
        try {
            $dto=UpdateStockDto::fromRequest($request);
            $stock = $this->stockService->update($id, $dto, Auth::id());
            return $this->success($stock, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un véhicule du stock.
     * DELETE /api/stocks/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        if (! $this->stockService->delete($id)) {
            return response()->json(['message' => 'Véhicule introuvable.'], 404);
        }

        return response()->json(['message' => 'Véhicule supprimé du stock.']);
    }

    /**
     * Changer le dépôt d'un véhicule (transfert).
     * PATCH /api/stocks/{id}/depot
     */
    public function changeDepot(ChangeDepotStockRequest $request, int $id): JsonResponse
    {
        $stock = $this->stockService->changeDepot($id, $request->toDto(), Auth::id());

        if (! $stock) {
            return response()->json(['message' => 'Véhicule introuvable.'], 404);
        }

        return response()->json([
            'message' => 'Dépôt mis à jour avec succès.',
            'data' => $stock,
        ]);
    }
    // import fichier excel
    public function importStock(Request $request): JsonResponse
    {

    }
}
