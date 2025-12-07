<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases middleware kustom aplikasi
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
            'account.active' => \App\Http\Middleware\CheckAccountActive::class,
        ]);
        
        // Apply CheckAccountActive to all authenticated routes
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckAccountActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();