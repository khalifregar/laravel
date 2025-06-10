<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Paksa selalu return JSON biar ga redirect
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Default fallback biar ga error kalo masih ke-trigger
        abort(401, 'Unauthorized');
    }
}
