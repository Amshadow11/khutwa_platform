<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'auth'         => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest'        => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'throttle'     => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'auth.company' => \App\Http\Middleware\EnsureCompanyAuthenticated::class,
        ]);

        $middleware->redirectGuestsTo(fn() => route('login'));
    })

    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير مصرح'], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير موجود'], 404);
            }
            return response()->view('errors.404', [], 404);
        });

    })->create();
