<?php

use App\Models\LoginActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // A guest whose session id has a login record had a session that is gone
        // (forced logout or timeout) — tell them on the login page.
        $middleware->redirectGuestsTo(function (Request $request) {
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;

            if ($sessionId !== null && LoginActivity::where('session_id', $sessionId)->exists()) {
                $request->session()->flash('session_expired', true);
            }

            return route('login');
        });

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withEvents(discover: true)
    ->create();
