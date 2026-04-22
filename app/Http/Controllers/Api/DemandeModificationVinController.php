<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeModificationVin\IndexDemandeModificationVinRequest;
use App\Http\Requests\DemandeModificationVin\StoreDemandeModificationVinRequest;
use App\Http\Requests\DemandeModificationVin\RefuserDemandeModificationVinRequest;
use App\Http\Resources\DemandeModificationVinResource;
use App\Models\DemandeModificationVin;
use App\Services\DemandeReservation\DemandeModificationVinService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DemandeModificationVinController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly DemandeModificationVinService $service,
    ) {}

    /**
     * List VIN modification requests.
     * By default returns pending (en_attente) requests — suitable for the admin dashboard.
     */
    public function index(IndexDemandeModificationVinRequest $request): JsonResponse
    {
        try {
            $data = $this->service->list($request->validated());

            if ($data instanceof Collection) {
                return $this->success(DemandeModificationVinResource::collection($data), MessageKey::FETCHED);
            }

            return $this->success($data, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * Create a new VIN modification request (any authenticated user).
     */
    public function store(StoreDemandeModificationVinRequest $request): JsonResponse
    {
        try {
            $row = $this->service->create($request->validated(), Auth::id());

            return $this->success(
                new DemandeModificationVinResource($row->load(['demandeReservation', 'stock', 'demandeur'])),
                MessageKey::CREATED,
                201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->error(MessageKey::INVALID, $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * Show a single VIN modification request.
     */
    public function show(DemandeModificationVin $demande_modification_vin): JsonResponse
    {
        $row = $this->service->find($demande_modification_vin->id);

        return $this->success(new DemandeModificationVinResource($row), MessageKey::FETCHED);
    }

    /**
     * Admin: approve a VIN modification request (triggers the actual VIN change).
     */
    public function approuver(DemandeModificationVin $demande_modification_vin): JsonResponse
    {
        try {
            $updated = $this->service->approuver($demande_modification_vin, Auth::id());

            if (! $updated) {
                return $this->error(MessageKey::INVALID, 'La demande ne peut pas être approuvée (déjà traitée ou introuvable).', 422);
            }

            return $this->success(new DemandeModificationVinResource($updated), MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * Admin: refuse a VIN modification request.
     */
    public function refuser(RefuserDemandeModificationVinRequest $request, DemandeModificationVin $demande_modification_vin): JsonResponse
    {
        try {
            $updated = $this->service->refuser(
                $demande_modification_vin,
                Auth::id(),
                $request->validated()['motif_refus'] ?? null
            );

            if (! $updated) {
                return $this->error(MessageKey::INVALID, 'La demande ne peut pas être refusée (déjà traitée ou introuvable).', 422);
            }

            return $this->success(new DemandeModificationVinResource($updated), MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    /**
     * Delete a VIN modification request.
     */
    public function destroy(DemandeModificationVin $demande_modification_vin): JsonResponse
    {
        if (! $this->service->delete($demande_modification_vin->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        return $this->success(null, MessageKey::DELETED);
    }
}
