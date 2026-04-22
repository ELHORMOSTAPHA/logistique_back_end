<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeMotif;
use App\Models\DemandeReservation;
use App\Models\Stock;
use App\Http\Resources\Livraison\LivraisonResource;
use App\Services\Livraison\LivraisonService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExternalSyncController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly LivraisonService $livraisonService,
    ) {}

    /**
     * Receive an order from SOUEAST-CRM and save it as a demande_reservation.
     * stock_id is linked if a stock with the given VIN exists, otherwise left null.
     */
    public function syncCommande(Request $request): JsonResponse
    {
        $data = $request->validate([
            'numero_commande'              => 'required|string|max:45',
            'vin'                          => 'nullable|string|max:45',
            'id_stock'                     => 'nullable|integer',
            'expose'                       => 'nullable|boolean',
            'in_arrivage'                  => 'nullable|boolean',
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

        // Find stock: prefer id_stock, fall back to VIN
        $stock = null;
        if (! empty($data['id_stock'])) {
            $stock = Stock::find($data['id_stock']);
        }
        if ($stock === null && ! empty($data['vin'])) {
            $stock = Stock::where('vin', $data['vin'])->first();
        }

        // Mark stock as reserved when found
        if ($stock !== null) {
            $stock->reserved = true;
            $stock->save();
        }

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
                    $decoded = base64_decode(preg_replace('/\s+/', '', $motif['file_content']), true);
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

    /**
     * Reçoit une demande de livraison d'un système externe (CRM, ERP…).
     *
     * POST /api/integration/livraison
     * Body: { vin, nom_client, tel_client, cmd_id }
     *
     * - Localise le stock par VIN.
     * - Si une livraison active (en_attente / facturé) existe déjà pour ce stock → renvoie 200.
     * - Sinon crée la livraison + entrée historique → renvoie 201.
     * - VIN inconnu → 422.
     */
    public function storeLivraison(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vin'        => 'required|string|max:45',
            'nom_client' => 'required|string|max:255',
            'tel_client' => 'nullable|string|max:50',
            'cmd_id'     => 'nullable|string|max:100',
        ]);

        $result = $this->livraisonService->createFromIntegration($data);

        if ($result['livraison'] === null) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun véhicule trouvé pour ce VIN.',
                'data'    => null,
            ], 422);
        }

        $statusCode = $result['created'] ? 201 : 200;
        $message    = $result['created']
            ? 'Livraison créée avec succès.'
            : 'Une livraison active existe déjà pour ce véhicule.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => new LivraisonResource($result['livraison']),
        ], $statusCode);
    }
}
