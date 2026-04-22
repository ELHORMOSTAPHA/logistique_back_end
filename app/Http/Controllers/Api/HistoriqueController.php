<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Historique\IndexHistoriqueRequest;
use App\Http\Requests\Historique\StoreHistoriqueRequest;
use App\Http\Requests\Historique\UpdateHistoriqueRequest;
use App\Models\Historique;
use App\Services\Historique\HistoriqueService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HistoriqueController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly HistoriqueService $historiqueService,
    ) {}

    public function index(IndexHistoriqueRequest $request): JsonResponse
    {
        try {
            $data = $this->historiqueService->list($request->validated());

            return $this->success($data, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreHistoriqueRequest $request): JsonResponse
    {
        try {
            $row = $this->historiqueService->create($request->validated(), Auth::id());

            return $this->success($this->historiqueService->presentForApi($row), MessageKey::CREATED, 201);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(Historique $historique): JsonResponse
    {
        return $this->success($this->historiqueService->presentForApi($historique), MessageKey::FETCHED);
    }

    public function update(UpdateHistoriqueRequest $request, Historique $historique): JsonResponse
    {
        try {
            $updated = $this->historiqueService->update($historique->id, $request->validated());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            return $this->success($this->historiqueService->presentForApi($updated), MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function destroy(Historique $historique): JsonResponse
    {
        if (! $this->historiqueService->delete($historique->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        return $this->success(null, MessageKey::DELETED);
    }
}
