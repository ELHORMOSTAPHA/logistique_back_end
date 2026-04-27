<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Models\StockStatus;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class StockStatusController extends Controller
{
    use ApiResponsable;

    /**
     * Liste des statuts stock (sélecteurs, formulaires).
     * GET /api/stock-statuses
     */
    public function index(): JsonResponse
    {
        $rows = StockStatus::query()
            ->where('is_available_for_update', true)
            ->orderBy('id')
            ->get(['id', 'libelle']);

        return $this->success($rows, MessageKey::FETCHED);
    }
}
