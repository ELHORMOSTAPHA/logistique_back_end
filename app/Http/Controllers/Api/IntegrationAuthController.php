<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Models\IntegrationClient;
use App\Traits\ApiResponsable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class IntegrationAuthController extends Controller
{
    use ApiResponsable;

    public function token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = IntegrationClient::where('client_id', (string) $request->string('client_id'))->first();

        if (! $client || ! Hash::check((string) $request->string('client_secret'), $client->client_secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client credentials.',
            ], 401);
        }

        if (! $client->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Integration client is inactive.',
            ], 403);
        }

        $expiresIn = 86400;
        $token = JWTHelper::generateIntegrationToken(
            $client->id,
            $client->client_id,
            $expiresIn
        );

        $client->last_used_at = Carbon::now();
        $client->save();

        return response()->json([
            'success' => true,
            'message' => 'Integration token generated successfully.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
            ],
        ]);
    }
}
