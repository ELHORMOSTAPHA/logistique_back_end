<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeReservation\IndexDemandeReservationRequest;
use App\Http\Requests\DemandeReservation\StoreDemandeReservationRequest;
use App\Http\Requests\DemandeReservation\UpdateDemandeReservationRequest;
use App\Http\Requests\DemandeReservation\AffecterVinRequest;
use App\Http\Resources\DemandeReservationResource;
use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Services\DemandeReservation\DemandeReservationService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;


class DemandeReservationController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly DemandeReservationService $demandeReservationService,
    ) {}

    public function index(IndexDemandeReservationRequest $request): JsonResponse
    {
        try {
            $data = $this->demandeReservationService->list($request->validated());
            if ($data instanceof Collection) {
                return $this->success(DemandeReservationResource::collection($data), MessageKey::FETCHED);
            }
            return $this->success($data, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreDemandeReservationRequest $request): JsonResponse
    {
        try {
            $row = $this->demandeReservationService->create($request->toDto());
            if ($row instanceof Collection) {
                return $this->success(DemandeReservationResource::collection($row), MessageKey::CREATED, 201);
            }
            return $this->success($row, MessageKey::CREATED, 201);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(DemandeReservation $demande_reservation): JsonResponse
    {
        $row = $this->demandeReservationService->find($demande_reservation->id);

        return $this->success($row, MessageKey::FETCHED);
    }

    public function update(UpdateDemandeReservationRequest $request, DemandeReservation $demande_reservation): JsonResponse
    {
        try {
            $updated = $this->demandeReservationService->update($demande_reservation->id, $request->validated());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function destroy(DemandeReservation $demande_reservation): JsonResponse
    {
        if (! $this->demandeReservationService->delete($demande_reservation->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        return $this->success(null, MessageKey::DELETED);
    }

    public function matchingStock(DemandeReservation $demande_reservation): JsonResponse
    {
        try {
            $stocks = $this->demandeReservationService->getMatchingStock($demande_reservation);
            return $this->success($stocks, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function affecterVin(AffecterVinRequest $request, DemandeReservation $demande_reservation): JsonResponse
    {
        try {
            $updated = $this->demandeReservationService->affecterVin($demande_reservation, $request->validated());
            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function matchingVin(DemandeReservation $demande_reservation): JsonResponse
    {
        try {
            $stocks = $this->demandeReservationService->getMatchingVinStock($demande_reservation);
            return $this->success($stocks, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function matchingVinByStock(Stock $stock): JsonResponse
    {
        try {
            $row = $this->demandeReservationService->getVinStockRowForAffectation($stock->id);
            if ($row === null) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            return $this->success($row, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function modifierVin(AffecterVinRequest $request, DemandeReservation $demande_reservation): JsonResponse
    {
        try {
            $updated = $this->demandeReservationService->modifierVin($demande_reservation, $request->validated());
            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }
            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }
}
