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
    ->withMiddleware(function (Middleware $middleware): void {
    // Trust reverse-proxy headers (e.g. Render/Heroku terminate TLS and forward
    // X-Forwarded-Proto). Without this, HTTPS requests look like HTTP and Laravel
    // generates http:// asset URLs, which browsers block as mixed content.
    $middleware->trustProxies(at: '*');

    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class, // <-- custom
    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    