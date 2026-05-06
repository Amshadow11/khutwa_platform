<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureCompanyAuthenticated;
use App\Http\Middleware\EnsureCompanyVerified;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'auth'         => Authenticate::class,

            'guest'        => RedirectIfAuthenticated::class,
            'throttle'     => ThrottleRequests::class,
            'auth.company' => EnsureCompanyAuthenticated::class,
            'company.verified' => EnsureCompanyVerified::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);

        $middleware->redirectGuestsTo(fn() => route('login'));
    })

    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (
            AuthorizationException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير مصرح'], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (
            NotFoundHttpException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير موجود'], 404);
            }
            return response()->view('errors.404', [], 404);
        });

    })->create();
