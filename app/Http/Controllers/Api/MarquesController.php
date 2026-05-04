<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Models\Marque;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class MarquesController extends Controller
{
    use ApiResponsable;

    /**
     * Liste des marques (référentiel + filtres stock).
     * GET /api/marques
     */
    public function index(): JsonResponse
    {
        $rows = Marque::query()
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        return $this->success($rows, MessageKey::FETCHED);
    }
}
