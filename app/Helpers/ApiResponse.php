<?php
// app/Helpers/ApiResponse.php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use App\Enums\MessageKey;
class ApiResponse
{
    public static function success(
        mixed  $data    = null,
        MessageKey $message = MessageKey::FETCHED,
        int    $status  = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message->translate(),
            'data'    => $data,
        ], $status);
    }

    public static function error(
        MessageKey $message = MessageKey::SERVER,
        mixed  $errors  = null,
        int    $status  = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message->translate(),
            'data'    => $errors,
        ], $status);
    }

    /**
     * Success response with translated message merged with extra top-level keys
     * (e.g. auth: access_token, user) without nesting them under `data`.
     *
     * @param  array<string, mixed>  $body
     */
    public static function successFlat(
        array $body,
        MessageKey $message = MessageKey::FETCHED,
        int $status = 200
    ): JsonResponse {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message->translate(),
        ], $body), $status);
    }

    /**
     * Error response with translated message merged with extra top-level keys.
     *
     * @param  array<string, mixed>  $body
     */
    public static function errorFlat(
        array $body,
        MessageKey $message = MessageKey::SERVER,
        int $status = 400
    ): JsonResponse {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message->translate(),
        ], $body), $status);
    }
}