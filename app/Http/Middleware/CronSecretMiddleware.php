<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentification simplifiée pour les cron serveurs.
 *
 * Vérifie un secret partagé fourni dans l'un des en-têtes suivants :
 *  - `X-Cron-Secret: <secret>`
 *  - `Authorization: Bearer <secret>`
 *
 * Le secret est lu depuis `config('crm_soueast.cron_secret')` (variable `.env` : `CRON_SECRET`).
 * Comparaison en temps constant pour éviter l'attaque par timing.
 */
class CronSecretMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('crm_soueast.cron_secret', '');

        if ($expected === '') {
            return $this->unauthorized('Cron secret is not configured on the server.');
        }

        $provided = (string) ($request->header('X-Cron-Secret') ?? '');
        if ($provided === '') {
            $provided = (string) ($request->bearerToken() ?? '');
        }

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return $this->unauthorized('Invalid or missing cron secret.');
        }

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}
