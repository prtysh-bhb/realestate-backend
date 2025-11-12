<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = $request->user()->id;

        // Property Statistics
        $totalProperties = Property::where('agent_id', $agentId)->count();
        $publishedProperties = Property::where('agent_id', $agentId)
            ->where('status', 'published')
            ->count();
        $draftProperties = Property::where('agent_id', $agentId)
            ->where('status', 'draft')
            ->count();
        $soldProperties = Property::where('agent_id', $agentId)
            ->where('status', 'sold')
            ->count();
        $rentedProperties = Property::where('agent_id', $agentId)
            ->where('status', 'rented')
            ->count();
        
        // Approval Status
        $pendingApproval = Property::where('agent_id', $agentId)
            ->where('approval_status', 'pending')
            ->count();
        $approvedProperties = Property::where('agent_id', $agentId)
            ->where('approval_status', 'approved')
            ->count();
        $rejectedProperties = Property::where('agent_id', $agentId)
            ->where('approval_status', 'rejected')
            ->count();

        // Inquiry Statistics
        $totalInquiries = Inquiry::where('agent_id', $agentId)->count();
        $newInquiries = Inquiry::where('agent_id', $agentId)
            ->where('status', 'new')
            ->count();
        $contactedInquiries = Inquiry::where('agent_id', $agentId)
            ->where('status', 'contacted')
            ->count();
        $closedInquiries = Inquiry::where('agent_id', $agentId)
            ->where('status', 'closed')
            ->count();

        // Recent Inquiries (Last 7 days)
        $recentInquiries = Inquiry::where('agent_id', $agentId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // This Month Statistics
        $thisMonthProperties = Property::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $thisMonthInquiries = Inquiry::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Recent Properties (Latest 5)
        $recentProperties = Property::where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'price', 'status', 'approval_status', 'created_at']);

        // Recent Inquiries (Latest 5)
        $latestInquiries = Inquiry::with(['property:id,title', 'customer:id,name,email'])
            ->where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top Performing Properties (Most inquiries)
        $topProperties = Property::where('agent_id', $agentId)
            ->withCount('inquiries')
            ->orderBy('inquiries_count', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'price', 'status']);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'stats' => [
                    'properties' => [
                        'total' => $totalProperties,
                        'published' => $publishedProperties,
                        'draft' => $draftProperties,
                        'sold' => $soldProperties,
                        'rented' => $rentedProperties,
                        'pending_approval' => $pendingApproval,
                        'approved' => $approvedProperties,
                        'rejected' => $rejectedProperties,
                        'this_month' => $thisMonthProperties,
                    ],
                    'inquiries' => [
                        'total' => $totalInquiries,
                        'new' => $newInquiries,
                        'contacted' => $contactedInquiries,
                        'closed' => $closedInquiries,
                        'recent' => $recentInquiries,
                        'this_month' => $thisMonthInquiries,
                    ],
                ],
                'recent_properties' => $recentProperties,
                'recent_inquiries' => $latestInquiries,
                'top_properties' => $topProperties,
            ],
        ]);
    }
}