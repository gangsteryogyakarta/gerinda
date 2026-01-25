<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Append security headers to all web requests
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        // Configure API rate limiting
        $middleware->throttleWithRedis();
        
        // Alias for custom middleware
        $middleware->alias([
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
            'log.suspicious' => \App\Http\Middleware\LogSuspiciousActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Don't report certain exceptions to logs
        $exceptions->dontReport([
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ]);
    })->create();

