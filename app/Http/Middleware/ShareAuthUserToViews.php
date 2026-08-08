<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ShareAuthUserToViews
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->cookie('jwt_token');

            if (!$token) {
                Log::warning('JwtWebMiddleware: no jwt_token cookie', [
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                ]);

                session()->put('url.intended', $request->fullUrl());
                return redirect()->route('login');
            }

            $user = JWTAuth::setToken($token)->authenticate();
            /** @var \App\Models\User $user */
            // ── Share to all Blade views ──────────────────────────
            View::share('authUser', $user);
            View::share('authUserRoles', $user->getRoleNames()->toArray());
            View::share('authUserPerms', $user->getAllPermissions()->pluck('name')->toArray());

        } catch (JWTException $e) {
            Log::warning('JwtWebMiddleware: ' . $e->getMessage(), [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
        }

        return $next($request);
    }
}