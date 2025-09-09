<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRevokedToken
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('student-api')->check()) {
            $token = $request->user('student-api')->token();

            if ($token && $token->revoked) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have been logged out from another device.',
                ], 401);
            }
        }

        return $next($request);
    }
}
