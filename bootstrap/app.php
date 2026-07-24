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
    
    ->withMiddleware(function ($middleware) {
    $middleware->alias([
        'jwt' => \App\Http\Middleware\JwtMiddleware::class,
        'jwt.web' => \App\Http\Middleware\JwtWebMiddleware::class,
        'admin.only' => \App\Http\Middleware\AdminMiddleware::class,
        'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    ]);
    $middleware->validateCsrfTokens(except: [
    '/logout',
    '/admin/logout',
]);
$middleware->api(append: [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
]);

    
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
