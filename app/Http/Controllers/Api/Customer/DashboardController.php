<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Inquiry;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Get favorites count
        $favoritesCount = Favorite::where('user_id', $userId)->count();

        // Get inquiries count
        $inquiriesCount = Inquiry::where('customer_id', $userId)->count();

        // Get recent inquiries with property and agent details
        $recentInquiries = Inquiry::with(['property:id,title,price,location', 'agent:id,name,email'])
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent favorites with property and agent details
        $recentFavorites = Favorite::with(['property:id,title,price,location,agent_id', 'property.agent:id,name,email'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Welcome to Customer Dashboard',
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ],
                'summary' => [
                    'total_favorites' => $favoritesCount,
                    'total_inquiries' => $inquiriesCount,
                ],
                'recent_inquiries' => $recentInquiries,
                'recent_favorites' => $recentFavorites,
            ],
        ]);
    }
}