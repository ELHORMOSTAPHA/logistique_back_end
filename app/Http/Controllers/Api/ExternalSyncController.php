<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeMotif;
use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'numero_commande'              => 'required|string|max:45',
            'vin'                          => 'nullable|string|max:45',
            'vendeur'                      => 'required|string|max:45',
            'date_commande'                => 'required|date_format:Y-m-d',
            'date_livraison'               => 'required|date_format:Y-m-d',
            'net_a_payer'                  => 'required|numeric|min:0',
            'statut'                       => 'sometimes|string|max:45',
            'vehicle_marque'               => 'nullable|string|max:100',
            'vehicle_modele'               => 'nullable|string|max:100',
            'vehicle_finition'             => 'nullable|string|max:100',
            'vehicle_color_ex'             => 'nullable|string|max:100',
            'vehicle_color_int'            => 'nullable|string|max:100',
            'motifs'                       => 'sometimes|array',
            'motifs.*.motifs_description'  => 'nullable|string|max:45',
            'motifs.*.file_path'           => 'nullable|string|max:255',
            'motifs.*.file_type'           => 'nullable|string|max:45',
            'motifs.*.file_content'        => 'nullable|string',
            'motifs.*.file_name'           => 'nullable|string|max:255',
        ]);

        // Try to find matching stock by VIN — optional
        $stock = ! empty($data['vin'])
            ? Stock::where('vin', $data['vin'])->first()
            : null;

        // Always create/update the demande regardless of stock match
        $demande = DemandeReservation::updateOrCreate(
            ['id_demande' => $data['numero_commande']],
            [
                'stock_id'          => $stock?->id,
                'vin'               => $data['vin'] ?? null,
                'nom_commercial'    => $data['vendeur'],
                'date_commande'     => $data['date_commande'],
                'date_livraison'    => $data['date_livraison'],
                'net_a_payer'       => $data['net_a_payer'],
                'statut'            => $data['statut'] ?? 'en cours',
                'vehicle_marque'    => $data['vehicle_marque'] ?? null,
                'vehicle_modele'    => $data['vehicle_modele'] ?? null,
                'vehicle_finition'  => $data['vehicle_finition'] ?? null,
                'vehicle_color_ex'  => $data['vehicle_color_ex'] ?? null,
                'vehicle_color_int' => $data['vehicle_color_int'] ?? null,
            ]
        );

        if (!empty($data['motifs'])) {
            $demande->demandeMotifs()->delete();
            foreach ($data['motifs'] as $motif) {
                $storedPath = null;

                if (!empty($motif['file_content']) && !empty($motif['file_name'])) {
                    $decoded = base64_decode($motif['file_content'], strict: true);
                    if ($decoded !== false) {
                        $ext        = pathinfo($motif['file_name'], PATHINFO_EXTENSION);
                        $filename   = 'demande_motifs/' . $demande->id . '_' . uniqid() . ($ext ? '.' . $ext : '');
                        Storage::disk('public')->put($filename, $decoded);
                        $storedPath = $filename; // relative: demande_motifs/xxx.ext
                    }
                }

                DemandeMotif::create([
                    'demandes_reservation_id' => $demande->id,
                    'motifs_description'      => $motif['motifs_description'] ?? null,
                    'file_path'               => $storedPath ?? $motif['file_path'] ?? null,
                    'file_type'               => $motif['file_type'] ?? null,
                ]);
            }
        }

        $statusCode = $demande->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'message' => $demande->wasRecentlyCreated ? 'Demande créée.' : 'Demande mise à jour.',
            'data'    => $demande->load('demandeMotifs'),
        ], $statusCode);
    }
}
