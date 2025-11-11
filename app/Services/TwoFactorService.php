<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret()
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function getQRCodeUrl($user, $secret)
    {
        $companyName = config('app.name', 'Laravel');
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secret
        );
    }

    public function verifyCode($secret, $code)
    {
        // Remove any whitespace from code
        $code = preg_replace('/\s+/', '', $code);
        
        // Ensure code is exactly 6 digits
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code, 2); // 2 = window tolerance
    }

    public function enable(User $user, $code)
    {
        if (!$user->two_factor_secret) {
            throw new \Exception('2FA secret not generated');
        }

        $secret = decrypt($user->two_factor_secret);

        if (!$this->verifyCode($secret, $code)) {
            throw new \Exception('Invalid verification code');
        }

        $user->two_factor_enabled = true;
        $user->save();

        return true;
    }

    public function disable(User $user, $code)
    {
        $secret = decrypt($user->two_factor_secret);

        if (!$this->verifyCode($secret, $code)) {
            throw new \Exception('Invalid verification code');
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();

        return true;
    }
}