<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\ArticleService;

class AuthController extends Controller
{
    public function login(Request $request, ArticleService $articleService)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {

            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('Login failed: invalid credentials', [
                    'email' => $credentials['email'],
                    'ip'    => $request->ip(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Invalid email or password.']);
            }
            $user = JWTAuth::user();
            $articleService->linkSubmissionsAndAssignAuthorRole($user);

            Log::info('Login successful', [
                'email' => $credentials['email'],
                'ip'    => $request->ip(),
            ]);

            // intended() returns user to the page they tried to visit before login
            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', 'Login successful.')
                ->cookie(
                    'jwt_token',
                    $token,
                    60,
                    '/',
                    null,
                    app()->isProduction(),
                    true,
                    false,
                    'Lax'
                );
        } catch (JWTException $e) {
            Log::error('Login: could not create token - ' . $e->getMessage(), [
                'email' => $credentials['email'] ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors(['email' => 'Could not create token. Please try again.']);
        }
    }


    public function register(Request $request, ArticleService $articleService)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $articleService->linkSubmissionsAndAssignAuthorRole($user);

            Log::info('New user registered', [
                'email' => $user->email,
                'ip'    => $request->ip(),
            ]);

            // Auto-login after registration and issue JWT cookie
            $token = JWTAuth::fromUser($user);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Account created successfully. Welcome!')
                ->cookie(
                    'jwt_token',
                    $token,
                    60,
                    '/',
                    null,
                    app()->isProduction(),
                    true,
                    false,
                    'Lax'
                );
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage(), [
                'email' => $data['email'] ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->cookie('jwt_token');

            if (!$token) {
                Log::warning('Logout attempted with no token cookie present', [
                    'ip' => $request->ip(),
                ]);

                return response()
                    ->json(['status' => false, 'message' => 'Token not found.'], 401)
                    ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
            }

            JWTAuth::setToken($token)->invalidate();

            Log::info('Logout successful', ['ip' => $request->ip()]);

            return response()
                ->json(['status' => true, 'message' => 'Logged out successfully.'])
                ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
        } catch (TokenExpiredException $e) {
            Log::info('Logout: token already expired', ['ip' => $request->ip()]);

            return response()
                ->json(['status' => true, 'message' => 'Logged out successfully.'])
                ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
        } catch (TokenBlacklistedException $e) {
            Log::info('Logout: token already blacklisted', ['ip' => $request->ip()]);

            return response()
                ->json(['status' => true, 'message' => 'Logged out successfully.'])
                ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
        } catch (TokenInvalidException $e) {
            Log::warning('Logout: invalid token - ' . $e->getMessage(), ['ip' => $request->ip()]);

            return response()
                ->json(['status' => false, 'message' => 'Invalid token.'], 401)
                ->cookie('jwt_token', '', -1, '/', null, app()->isProduction(), true, false, 'Lax');
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage(), ['ip' => $request->ip()]);

            return response()->json([
                'status'  => false,
                'message' => 'Logout failed.',
            ], 500);
        }
    }
}
