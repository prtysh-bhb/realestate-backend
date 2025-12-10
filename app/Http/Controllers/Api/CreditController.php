<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Credit;

class CreditController extends Controller
{
    /**
     * Public endpoint - No authentication required
     * Show all active credit packages for pricing page
     */
    public function packages()
    {
        $packages = Credit::where('status', 'active')
            ->orderBy('coins', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }
}