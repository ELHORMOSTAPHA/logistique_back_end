<?php

namespace App\Exceptions;

use App\Enums\MessageKey;
use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::error(MessageKey::NOT_FOUND, null, 404);
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::error(MessageKey::ROUTE, null, 404);
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error(MessageKey::INVALID, $e->errors(), 422);
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::error(MessageKey::FORBIDDEN, null, 403);
            }
        }

        return parent::render($request, $e);
    }
}
