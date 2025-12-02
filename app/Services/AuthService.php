<?php

namespace App\Services;

use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            // 'role' => "admin",
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Send welcome email
        if(env('APP_ENV') == 'production'){
            Mail::to($user->email)->send(new WelcomeMail($user));
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        Log::info('AuthService:34', [
            'user' => $user,
            'pass' => Hash::check($credentials['password'], $user->password)
        ]);
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw new \Exception('Your account is inactive.');
        }

        // Check if 2FA is enabled (0 or 1)
        if ($user->two_factor_enabled == 1) {
            // 2FA is enabled - ask for code
            return [
                'requires_2fa' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => 'Please provide your 2FA code',
            ];
        }

        // 2FA is not enabled (0) - login directly
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'requires_2fa' => false,
            'user' => $user,
            'token' => $token,
        ];
    }

    public function verifyAndLogin($email, $code)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->two_factor_enabled) {
            throw new \Exception('Invalid request');
        }

        $twoFactorService = new \App\Services\TwoFactorService();
        $secret = decrypt($user->two_factor_secret);

        if (!$twoFactorService->verifyCode($secret, $code)) {
            throw new \Exception('Invalid 2FA code');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
        return true;
    }
}