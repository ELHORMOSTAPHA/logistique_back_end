<?php
// app/Traits/ApiResponsable.php
namespace App\Traits;

use App\Enums\MessageKey;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponsable
{
    protected function success(
        mixed      $data    = null,
        MessageKey $message = MessageKey::FETCHED,
        int        $status  = 200
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status);
    }

    protected function error(
        MessageKey $message = MessageKey::SERVER,
        mixed      $errors  = null,
        int        $status  = 400
    ): JsonResponse {
        return ApiResponse::error($message, $errors, $status);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function successFlat(
        array $body,
        MessageKey $message = MessageKey::FETCHED,
        int $status = 200
    ): JsonResponse {
        return ApiResponse::successFlat($body, $message, $status);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function errorFlat(
        array $body,
        MessageKey $message = MessageKey::SERVER,
        int $status = 400
    ): JsonResponse {
        return ApiResponse::errorFlat($body, $message, $status);
    }
}