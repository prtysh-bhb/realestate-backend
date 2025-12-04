<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to provider
     */
    public function redirectToProvider($provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->stateless()->redirect();
    }
    
    /**
     * Handle provider callback
     */
    public function handleProviderCallback($provider)
    {
        $this->validateProvider($provider);

        try {
            // Disable SSL verification for local development
            if (app()->environment('local')) {
                config(["services.{$provider}.guzzle" => ['verify' => false]]);
            }
            
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => Hash::make(uniqid()),
                    'role' => 'customer',
                    'is_active' => true,
                ]);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Minimal user payload (avoid leaking sensitive fields)
            $userPayload = $user->only(['id', 'name', 'email', 'role', 'avatar']);
            $userJson = json_encode($userPayload);

            // Base64 encode to make it URL safe
            $userB64 = base64_encode($userJson);

            // Get frontend URL from .env
            $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');

            // Build redirect URL. We'll pass token and user (base64) as query params.
            $redirectUrl = $frontend . '/social-callback?token=' . urlencode($token) . '&user=' . urlencode($userB64) . '&provider=' . urlencode($provider);

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            // Get frontend URL from .env
            $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
            $errorUrl = $frontend . '/social-callback?error=' . urlencode($e->getMessage());
            return redirect($errorUrl);
        }
    }
    
    private function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            abort(404, 'Invalid provider');
        }
    }
}