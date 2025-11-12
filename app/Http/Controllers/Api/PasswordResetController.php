<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link via email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate token
        $token = Str::random(64);

        // Delete old tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send email
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        try {
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password - Real Estate Platform');
            });

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Get token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token',
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token',
            ], 400);
        }

        // Check if token is expired (24 hours)
        $tokenAge = now()->diffInHours($tokenRecord->created_at);
        if ($tokenAge > 24) {
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete all tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ]);
    }

    /**
     * Verify if token is valid (optional endpoint)
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 400);
        }

        if (!Hash::check($request->token, $tokenRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 400);
        }

        // Check expiry
        $tokenAge = now()->diffInHours($tokenRecord->created_at);
        if ($tokenAge > 24) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
        ]);
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use sendResetLink instead
     */
    public function forgotPassword(Request $request)
    {
        return $this->sendResetLink($request);
    }
}