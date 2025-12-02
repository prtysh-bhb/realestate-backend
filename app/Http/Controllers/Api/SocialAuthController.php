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
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if ($user) {
                // Update existing user
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => Hash::make(uniqid()), // Random password
                    'role' => 'customer', // Default role
                    'is_active' => true,
                ]);
            }
            
            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    private function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            abort(404, 'Invalid provider');
        }
    }
}