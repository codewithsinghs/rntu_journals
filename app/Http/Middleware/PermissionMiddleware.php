<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        try {
            $token = $request->cookie('jwt_token');

            if (!$token) {
                return $this->deny($request, 'Unauthenticated.', 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return $this->deny($request, 'Unauthenticated.', 401);
            }
/** @var \App\Models\User $user */
            if (!$user->hasPermissionTo($permission)) {// ← can() ki jagah yeh use karo
    return $this->deny($request, 'Access denied. Missing permission: ' . $permission);
}

            return $next($request);

        } catch (JWTException $e) {
            return $this->deny($request, 'Invalid or expired token.', 401);
        }
    }

    private function deny(Request $request, string $message, int $status = 403)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->route('login')->withErrors(['error' => $message]);
    }
}