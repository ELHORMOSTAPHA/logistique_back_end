<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Lot\UpdateLotDto;
use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lot\IndexLotRequest;
use App\Http\Requests\Lot\StoreLotRequest;
use App\Http\Requests\Lot\UpdateLotRequest;
use App\Models\Lot;
use App\Services\Lot\LotService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LotController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly LotService $lotService,
    ) {}

    public function index(IndexLotRequest $request): JsonResponse
    {
        try {
            $lots = $this->lotService->list($request->toFilterDto());

            return $this->success($lots, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreLotRequest $request): JsonResponse
    {
        try {
            $lot = $this->lotService->create($request->toDto(), Auth::id());

            return $this->success($lot, MessageKey::CREATED, 201);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(Lot $lot): JsonResponse
    {
        return $this->success($lot, MessageKey::FETCHED);
    }

    public function update(UpdateLotRequest $request, Lot $lot): JsonResponse
    {
        try {
            $dto = UpdateLotDto::fromRequest($request);
            $updated = $this->lotService->update($lot->id, $dto, Auth::id());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function destroy(Lot $lot): JsonResponse
    {
        if (! $this->lotService->delete($lot->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        return $this->success(null, MessageKey::DELETED);
    }
}
