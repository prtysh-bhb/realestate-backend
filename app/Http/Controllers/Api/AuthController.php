<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\RestrictedDomain;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email', new RestrictedDomain()],
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:agent,customer',
        ]);

        $result = $this->authService->register($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => $result['user']->role,
                ],
                'token' => $result['token'],
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $result = $this->authService->login($request->only('email', 'password'));

            // Check if account is active
            if (isset($result['user']) && !$result['user']->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.',
                    'data' => [
                        'reason' => $result['user']->deactivation_reason,
                        'deactivated_at' => $result['user']->deactivated_at,
                    ]
                ], 403);
            }

            // If 2FA is enabled (two_factor_enabled = 1)
            if ($result['requires_2fa']) {
                return response()->json([
                    'success' => true,
                    'requires_2fa' => true,
                    'email' => $result['email'],
                    'message' => $result['message'],
                ]);
            }

            // If 2FA is not enabled (two_factor_enabled = 0) - direct login
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'role' => $result['user']->role,
                    ],
                    'token' => $result['token'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function verifyLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
        ]);

        try {
            $result = $this->authService->verifyAndLogin($request->email, $request->code);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'role' => $result['user']->role,
                    ],
                    'token' => $result['token'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'two_factor_enabled' => $request->user()->two_factor_enabled,
                ],
            ],
        ]);
    }

    public function getRestrictedDomains()
    {
        $domains = config('app.restricted_domains');
        $emailDomains = $domains ? explode(',', $domains) : [];

        return response()->json([
            'success' => true,
            'domains' => $emailDomains,
        ]);
    }
}