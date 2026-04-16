<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalSyncController extends Controller
{
    use ApiResponsable;

    /**
     * Receive an order from SOUEAST-CRM and save it as a demande_reservation.
     * stock_id is linked if a stock with the given VIN exists, otherwise left null.
     */
    public function syncCommande(Request $request): JsonResponse
    {
        $data = $request->validate([
            'numero_commande' => 'required|string|max:45',
            'vin'             => 'nullable|string|max:45',
            'vendeur'         => 'required|string|max:45',
            'date_commande'   => 'required|date_format:Y-m-d',
            'date_livraison'  => 'required|date_format:Y-m-d',
            'net_a_payer'     => 'required|numeric|min:0',
            'statut'          => 'sometimes|string|max:45',
        ]);

        // Try to find matching stock by VIN — optional
        $stock = ! empty($data['vin'])
            ? Stock::where('vin', $data['vin'])->first()
            : null;

        // Always create/update the demande regardless of stock match
        $demande = DemandeReservation::updateOrCreate(
            ['id_demande' => $data['numero_commande']],
            [
                'stock_id'       => $stock?->id,
                'vin'            => $data['vin'] ?? null,
                'nom_commercial' => $data['vendeur'],
                'date_commande'  => $data['date_commande'],
                'date_livraison' => $data['date_livraison'],
                'net_a_payer'    => $data['net_a_payer'],
                'statut'         => $data['statut'] ?? 'en cours',
            ]
        );

        $statusCode = $demande->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'message' => $demande->wasRecentlyCreated ? 'Demande créée.' : 'Demande mise à jour.',
            'data'    => $demande,
        ], $statusCode);
    }
}
