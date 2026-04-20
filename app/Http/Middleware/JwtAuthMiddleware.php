<?php

namespace App\Http\Middleware;

use App\Enums\MessageKey;
use App\Helpers\JWTHelper;
use App\Models\User;
use App\Traits\ApiResponsable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    use ApiResponsable;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->error(MessageKey::AUTH_TOKEN_MISSING, null, 401);
        }
        try {
            $payload = JWTHelper::validateToken($token);
            if ($payload['type'] !== 'access') {
                return $this->error(MessageKey::AUTH_TOKEN_TYPE, null, 401);
            }
            $user = User::find($payload['sub']);
            if (! $user) {
                return $this->error(MessageKey::USER_NOT_FOUND, null, 404);
            }
            //merge profile id into request
            $request->merge(['user' => $user]);
            $request->setUserResolver(fn() => $user);
            Auth::setUser($user);

            return $next($request);
        } catch (\Exception $e) {
            return $this->error(
                MessageKey::AUTH_TOKEN_INVALID,
                ['error' => $e->getMessage()],
                401
            );
        }
    }
}
