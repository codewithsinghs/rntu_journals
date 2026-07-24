<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->cookie('jwt_token');

            if (!$token) {
                Log::warning('JwtMiddleware: no jwt_token cookie present', [
                    'path' => $request->path(),
                    'ip'   => $request->ip(),
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Token not provided.'
                ], 401);
            }

            JWTAuth::setToken($token)->authenticate();

        } catch (JWTException $e) {
            Log::warning('JwtMiddleware: ' . $e->getMessage(), [
                'path' => $request->path(),
                'ip'   => $request->ip(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired token.'
            ], 401);
        }

        return $next($request);
    }
}