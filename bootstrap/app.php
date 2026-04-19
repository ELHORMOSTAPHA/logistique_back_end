<?php

use App\Exceptions\Handler as AppExceptionHandler;
use App\Http\Middleware\IntegrationAuthMiddleware;
use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => JwtAuthMiddleware::class,
            'integration.auth' => IntegrationAuthMiddleware::class,
            'api.key'  => ApiKeyMiddleware::class,
        ]);
        $middleware->api(append: [
            SetLocaleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Retourner du JSON pour toutes les erreurs sur les routes /api/*
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e): bool {
            return $request->is('api/*');
        });
    })
    ->withSingletons([
        ExceptionHandler::class => AppExceptionHandler::class,
    ])
    ->create();
