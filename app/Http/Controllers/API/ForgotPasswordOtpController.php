<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordOtpController extends Controller
{
    private const OTP_TTL_MINUTES       = 10;
    private const MAX_ATTEMPTS          = 5;
    private const RESEND_COOLDOWN_SECS  = 60;

    // ─── STEP 1: Generate + email an OTP for the given address ──────
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Please enter your registered email.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        $email = $validated['email'];

        try {
            $recent = DB::table('password_otp')->where('email', $email)->first();

            if ($recent) {
                $elapsed = (int) ceil(abs(now()->diffInSeconds($recent->created_at)));

                if ($elapsed < self::RESEND_COOLDOWN_SECS) {
                    $wait = self::RESEND_COOLDOWN_SECS - $elapsed;

                    return response()->json([
                        'status'  => false,
                        'message' => "Please wait {$wait}s before requesting another OTP.",
                    ], 429);
                }
            }

            $user = User::where('email', $email)->first();
            $otp = null;

            if ($user) {
                $otp = (string) random_int(100000, 999999);

                DB::table('password_otp')->where('email', $email)->delete();

                DB::table('password_otp')->insert([
                    'email'      => $email,
                    'otp'        => Hash::make($otp),
                    'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
                    'attempts'   => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Password reset OTP sent', [
                    'email' => $email,
                    'otp'   => $otp,
                ]);
            }

            $response = [
                'status'  => true,
                'message' => 'If that email is registered, an OTP has been sent to it.',
            ];

            
            if ($otp !== null) {
                $response['email'] = $email;
                $response['otp']   = $otp;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while sending the OTP. Please try again.',
            ], 500);
        }
    }

    // ─── STEP 2: Verify OTP + set the new password ──────────────────
    public function resetWithOtp(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'      => 'Email is required.',
            'otp.required'        => 'Please enter the OTP sent to your email.',
            'otp.size'            => 'OTP must be 6 digits.',
            'password.required'   => 'Please enter a new password.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.confirmed'  => 'Password confirmation does not match.',
        ]);

        $email  = $validated['email'];
        $record = DB::table('password_otp')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'No OTP request found for this email. Please request a new OTP.',
            ], 422);
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('password_otp')->where('email', $email)->delete();

            return response()->json([
                'status'  => false,
                'message' => 'This OTP has expired. Please request a new one.',
            ], 422);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_otp')->where('email', $email)->delete();

            return response()->json([
                'status'  => false,
                'message' => 'Too many incorrect attempts. Please request a new OTP.',
            ], 429);
        }

        if (!Hash::check($validated['otp'], $record->otp)) {
            DB::table('password_otp')->where('email', $email)->increment('attempts');

            return response()->json([
                'status'  => false,
                'message' => 'Incorrect OTP. Please try again.',
            ], 422);
        }

        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Account not found.',
                ], 404);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            DB::table('password_otp')->where('email', $email)->delete();

            Log::info('Password reset via OTP successful', ['email' => $email]);

            return response()->json([
                'status'  => true,
                'message' => 'Your password has been reset successfully. Please login.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset password via OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    // ─── STEP 1.5: Verify OTP only, before showing the password fields ──
public function verifyOtp(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'otp'   => 'required|string|size:6',
    ], [
        'email.required' => 'Email is required.',
        'otp.required'   => 'Please enter the OTP sent to your email.',
        'otp.size'       => 'OTP must be 6 digits.',
    ]);

    $email  = $validated['email'];
    $record = DB::table('password_otp')->where('email', $email)->first();

    if (!$record) {
        return response()->json([
            'status'  => false,
            'message' => 'No OTP request found for this email. Please request a new OTP.',
        ], 422);
    }

    if (now()->greaterThan($record->expires_at)) {
        DB::table('password_otp')->where('email', $email)->delete();

        return response()->json([
            'status'  => false,
            'message' => 'This OTP has expired. Please request a new one.',
        ], 422);
    }

    if ($record->attempts >= self::MAX_ATTEMPTS) {
        DB::table('password_otp')->where('email', $email)->delete();

        return response()->json([
            'status'  => false,
            'message' => 'Too many incorrect attempts. Please request a new OTP.',
        ], 429);
    }

    if (!Hash::check($validated['otp'], $record->otp)) {
        DB::table('password_otp')->where('email', $email)->increment('attempts');

        return response()->json([
            'status'  => false,
            'message' => 'Incorrect OTP. Please try again.',
        ], 422);
    }

    // OTP is correct — do NOT delete the record here, resetWithOtp()
    // still needs it to do the final password update.
    return response()->json([
        'status'  => true,
        'message' => 'OTP verified. You can now set a new password.',
    ]);
}
}