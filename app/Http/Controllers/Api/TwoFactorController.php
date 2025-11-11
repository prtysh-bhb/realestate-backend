<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is already enabled',
            ], 400);
        }

        $secret = $this->twoFactorService->generateSecret();
        $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user, $secret);

        $user->two_factor_secret = encrypt($secret);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => '2FA setup initiated',
            'data' => [
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl,
            ],
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();
        $secret = decrypt($user->two_factor_secret);

        try {
            $this->twoFactorService->enable($user, $request->code);

            return response()->json([
                'success' => true,
                'message' => '2FA enabled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();
        $secret = decrypt($user->two_factor_secret);

        try {
            $this->twoFactorService->disable($user, $request->code);

            return response()->json([
                'success' => true,
                'message' => '2FA disabled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled',
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);

        if ($this->twoFactorService->verifyCode($secret, $request->code)) {
            return response()->json([
                'success' => true,
                'message' => 'Code verified successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code',
        ], 400);
    }
}