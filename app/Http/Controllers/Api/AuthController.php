<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponsable;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorFlat(
                ['errors' => $validator->errors()],
                MessageKey::INVALID,
                422
            );
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->successFlat(
            ['user' => $user],
            MessageKey::USER_REGISTERED,
            201
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'fbm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorFlat(
                ['errors' => $validator->errors()],
                MessageKey::INVALID,
                422
            );
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error(MessageKey::AUTH_INVALID_CREDENTIALS, null, 401);
        }

        if ($user->statut !== 'actif') {
            return $this->error(MessageKey::ACCOUNT_INACTIVE, null, 403);
        }

        $user->load(['profile']);

        $accessToken = JWTHelper::generateAccessToken($user->id);
        $refreshToken = JWTHelper::generateRefreshToken($user->id);

        // return $this->successFlat([
        //     'access_token' => $accessToken,
        //     'refresh_token' => $refreshToken,
        //     'fbm_token' => $user->fbm_token,
        //     'token_type' => 'Bearer',
        //     'expires_in' => 3600,
        //     'user' => new UserResource($user),
        // ], MessageKey::LOGIN_SUCCESS);
    return $this->success([
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'fbm_token' => $user->fbm_token,
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'user' => new UserResource($user),
    ], MessageKey::LOGIN_SUCCESS);
    }

    public function me(Request $request)
    {
        return $this->userDetails($request);
    }

    public function userDetails(Request $request)
    {
        $user = User::with(['profile'])->find($request->user->id);

        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'profile_id' => $user->id_profile,
            'profile' => $user->profile,
            'prenom' => $user->prenom,
            'nom' => $user->nom,
            'telephone' => $user->telephone,
        ];

        return $this->successFlat(
            ['user' => $userData],
            MessageKey::FETCHED
        );
    }

    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorFlat(
                ['errors' => $validator->errors()],
                MessageKey::INVALID,
                422
            );
        }

        try {
            $payload = JWTHelper::validateToken($request->refresh_token);

            if ($payload['type'] !== 'refresh') {
                return $this->error(MessageKey::AUTH_TOKEN_TYPE_MISMATCH, null, 401);
            }

            $user = User::find($payload['sub']);

            if (! $user) {
                return $this->error(MessageKey::USER_NOT_FOUND, null, 404);
            }

            $accessToken = JWTHelper::generateAccessToken($user->id);
            $refreshToken = JWTHelper::generateRefreshToken($user->id);

            return $this->successFlat([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], MessageKey::TOKEN_REFRESHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::AUTH_REFRESH_FAILED, null, 401);
        }
    }

    public function logout(Request $request)
    {
        return $this->successFlat([], MessageKey::LOGOUT_SUCCESS);
    }
}
