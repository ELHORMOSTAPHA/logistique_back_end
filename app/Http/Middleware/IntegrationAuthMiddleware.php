<?php

namespace App\Http\Middleware;

use App\Helpers\JWTHelper;
use App\Models\IntegrationClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IntegrationAuthMiddleware
{
    /**
     * Handle an incoming request for machine-to-machine integrations.
     *
     * Expected header:
     * Authorization: Bearer <INTEGRATION_API_TOKEN>
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $this->unauthorized('Integration access token is missing.');
        }

        try {
            $payload = JWTHelper::validateToken($token);

            if (($payload['type'] ?? null) !== 'integration') {
                return $this->unauthorized('Invalid token type for integration access.');
            }

            $client = IntegrationClient::find($payload['sub'] ?? 0);
            if (! $client || ! $client->is_active) {
                return $this->unauthorized('Integration client is not active.');
            }

            $request->attributes->set('integration_client', $client);

            return $next($request);
        } catch (\Exception $e) {
            return $this->unauthorized('Integration token is invalid or expired.');
        }
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}
