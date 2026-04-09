<?php

namespace App\Http\Controllers\Api;

use App\DTOs\DemandeReservation\UpdateDemandeReservationDto;
use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeReservation\IndexDemandeReservationRequest;
use App\Http\Requests\DemandeReservation\StoreDemandeReservationRequest;
use App\Http\Requests\DemandeReservation\UpdateDemandeReservationRequest;
use App\Models\DemandeReservation;
use App\Services\DemandeReservation\DemandeReservationService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class DemandeReservationController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly DemandeReservationService $demandeReservationService,
    ) {}

    public function index(IndexDemandeReservationRequest $request): JsonResponse
    {
        try {
            $data = $this->demandeReservationService->list($request->toFilterDto());

            return $this->success($data, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreDemandeReservationRequest $request): JsonResponse
    {
        try {
            $row = $this->demandeReservationService->create($request->toDto());

            return $this->success($row->load('stock'), MessageKey::CREATED, 201);
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
            $dto = UpdateDemandeReservationDto::fromRequest($request);
            $updated = $this->demandeReservationService->update($demande_reservation->id, $dto);

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
}
