<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrmSoueast\CrmSoueastSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Synchronisation des référentiels véhicule depuis l'API CRM SOUEAST.
 *
 * Endpoint :
 *   POST /api/cron/sync/referentiel-vehicule
 *
 * Auth : middleware `cron.secret` (header `X-Cron-Secret` ou `Authorization: Bearer <secret>`).
 * Destiné à être appelé par un cron serveur, par ex. toutes les 6h :
 *
 *   curl -X POST https://<host>/api/cron/sync/referentiel-vehicule \
 *        -H "X-Cron-Secret: <CRON_SECRET>" \
 *        -H "Accept: application/json"
 */
class CrmSoueastSyncController extends Controller
{
    public function __construct(
        private readonly CrmSoueastSyncService $syncService,
    ) {}

    public function syncReferentielVehicule(Request $request): JsonResponse
    {
        // Empêche deux synchros concurrentes (cron qui se chevauche).
        $lock = Cache::lock('crm_soueast:sync_referentiel_vehicule', 600);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Une synchronisation est déjà en cours.',
                'data'    => null,
            ], 409);
        }

        try {
            $started = microtime(true);
            $result  = $this->syncService->syncAll();
            $durationMs = (int) round((microtime(true) - $started) * 1000);

            $this->audit(
                'cron.sync_referentiel_vehicule',
                null,
                null,
                null,
                [
                    'result'      => $result,
                    'duration_ms' => $durationMs,
                    'source_ip'   => $request->ip(),
                    'user_agent'  => (string) $request->header('User-Agent', ''),
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'Référentiel véhicule synchronisé depuis CRM SOUEAST.',
                'data'    => [
                    'duration_ms' => $durationMs,
                    'counters'    => $result,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('CRM SOUEAST sync referentiel-vehicule failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de la synchronisation : ' . $e->getMessage(),
                'data'    => null,
            ], 502);
        } finally {
            optional($lock)->release();
        }
    }
}
