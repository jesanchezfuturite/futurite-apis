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
    ->withMiddleware(function (Middleware $middleware) {
        // Excluir rutas específicas de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'atc/hook', // Excluye todas las rutas que empiecen con /stripe/
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
