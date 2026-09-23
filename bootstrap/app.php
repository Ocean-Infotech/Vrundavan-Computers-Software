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
        $middleware->alias([
            'device.authorized' => \App\Http\Middleware\CheckAuthorizedDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, \Throwable $e, $request) {
            if ($response->getStatusCode() === 419) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session expired. Please refresh and try again.'
                    ], 419);
                }

                if (\Illuminate\Support\Facades\Auth::check()) {
                    return redirect()->back()->withInput()->with('warning', 'Session expired. Please try submitting again.');
                }

                return redirect()->route('software.login')->with('warning', 'Session expired. Please login again.');
            }
            return $response;
        });
    })->create();
