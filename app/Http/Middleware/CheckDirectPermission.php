<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDirectPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = Auth::user();
        // dd("CheckDirectPermission 14", $request->all(), $next, $permission, $user, Auth::check());

        // If user is not logged in or doesn't have the required permission
        if (!$user || !$user->hasPermissionTo($permission)) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);

        if (!Auth::check() || !Auth::user()->permissions->contains('name', $permission)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
