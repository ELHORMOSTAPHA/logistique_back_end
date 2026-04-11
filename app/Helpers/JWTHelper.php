<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTHelper
{
    private static function getSecretKey(): string
    {
        $secret = config('app.jwt_secret');
        if (!\is_string($secret) || $secret === '') {
            throw new \RuntimeException('JWT_SECRET is not set. Add JWT_SECRET to .env and run php artisan config:clear if you use config caching.');
        }

        return $secret;
    }

    public static function generateToken($userId, $ttl = 60)
    {
        $issuedAt = time();
        $expire = $issuedAt + ($ttl * 60);

        $payload = [
            'iss' => config('app.url'),
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'type' => $ttl > 1440 ? 'refresh' : 'access'
        ];

        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }

    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public static function generateAccessToken($userId)
    {
        return self::generateToken($userId, 60); // 60 minutes
    }

    public static function generateRefreshToken($userId)
    {
        return self::generateToken($userId, 20160); // 14 days
    }
}