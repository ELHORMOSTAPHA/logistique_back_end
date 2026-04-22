<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Depot\IndexDepotRequest;
use App\Http\Requests\Depot\StoreDepotRequest;
use App\Http\Requests\Depot\UpdateDepotRequest;
use App\Models\Depot;
use App\Services\Depot\DepotService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DepotController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly DepotService $depotService,
    ) {}

    /**
     * Liste des dépôts (filtres, tri, pagination).
     * GET /api/depot
     */
    public function index(IndexDepotRequest $request): JsonResponse
    {
        try {
            $depots = $this->depotService->list($request->validated());

            return $this->success($depots, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    /**
     * POST /api/depot
     */
    public function store(StoreDepotRequest $request): JsonResponse
    {
        $depot = $this->depotService->create($request->validated(), Auth::id());
        $this->audit('create', 'depots', (int) $depot->id, null, $request->validated());

        return $this->success($depot, MessageKey::CREATED, 201);
    }

    /**
     * GET /api/depot/{depot}
     */
    public function show(Depot $depot): JsonResponse
    {
        return $this->success($depot, MessageKey::FETCHED);
    }

    /**
     * PUT/PATCH /api/depot/{depot}
     */
    public function update(UpdateDepotRequest $request, Depot $depot): JsonResponse
    {
        try {
            $updated = $this->depotService->update($depot->id, $request->validated());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            $this->audit('update', 'depots', $depot->id, null, $request->validated());

            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/depot/{depot}
     */
    public function destroy(Depot $depot): JsonResponse
    {
        if (! $this->depotService->delete($depot->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        $this->audit('delete', 'depots', $depot->id);

        return $this->success(null, MessageKey::DELETED);
    }
}
