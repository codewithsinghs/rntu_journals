<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            /** @var User $user */
            $user = JWTAuth::setToken($request->cookie('jwt_token'))->authenticate();

            if (!$user) {
                return $this->deny($request, 'Unauthenticated.');
            }

            // Allow if user has ANY of these roles
            $allowedRoles = ['admin', 'superadmin'];

            // OR allow if user has at least one permission (any assigned role has permissions)
            $hasRole        = $user->hasAnyRole($allowedRoles);
            $hasPermissions = $user->getAllPermissions()->isNotEmpty();

            if (!$hasRole && !$hasPermissions) {
                return $this->deny($request, 'Access denied. You do not have permission to access the admin panel.');
            }

            return $next($request);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->deny($request, 'Session expired. Please login again.', 401);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->deny($request, 'Invalid token. Please login again.', 401);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->deny($request, 'Token not found. Please login.', 401);

        } catch (\Exception $e) {
            return $this->deny($request, 'Unauthenticated.', 401);
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

        // Web request — redirect to login
        return redirect()->route('login')->withErrors(['error' => $message]);
    }
}