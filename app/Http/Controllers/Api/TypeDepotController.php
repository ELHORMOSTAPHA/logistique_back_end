<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Models\TypeDepot;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class TypeDepotController extends Controller
{
    use ApiResponsable;

    /**
     * Liste des types de dépôt pour les sélecteurs.
     * GET /api/type-depots
     */
    public function index(): JsonResponse
    {
        $rows = TypeDepot::query()
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        return $this->success($rows, MessageKey::FETCHED);
    }
}
