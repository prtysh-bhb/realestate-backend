<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\PropertyView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = $request->user()->id;

        // Single query to get all property stats with grouping
        $propertyStats = Property::where('agent_id', $agentId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published'),
                DB::raw('SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft'),
                DB::raw('SUM(CASE WHEN status = "sold" THEN 1 ELSE 0 END) as sold'),
                DB::raw('SUM(CASE WHEN status = "rented" THEN 1 ELSE 0 END) as rented'),
                DB::raw('SUM(CASE WHEN approval_status = "pending" THEN 1 ELSE 0 END) as pending_approval'),
                DB::raw('SUM(CASE WHEN approval_status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN approval_status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                DB::raw('SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month')
            )
            ->first();

        // Single query to get all inquiry stats with grouping
        $inquiryStats = Inquiry::where('agent_id', $agentId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new'),
                DB::raw('SUM(CASE WHEN status = "contacted" THEN 1 ELSE 0 END) as contacted'),
                DB::raw('SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed'),
                DB::raw('SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent'),
                DB::raw('SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month')
            )
            ->first();

        // Single query to get property views stats
        $viewStats = PropertyView::whereHas('property', function($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            })
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN MONTH(viewed_at) = MONTH(NOW()) AND YEAR(viewed_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month'),
                DB::raw('SUM(CASE WHEN DATE(viewed_at) = CURDATE() THEN 1 ELSE 0 END) as today')
            )
            ->first();

        // Efficient query for recent properties (single query with limit)
        $recentProperties = Property::where('agent_id', $agentId)
            ->select('id', 'title', 'price', 'status', 'approval_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Efficient query for recent inquiries with relationships (eager loading)
        $latestInquiries = Inquiry::with([
                'property:id,title', 
                'customer:id,name,email,avatar'
            ])
            ->where('agent_id', $agentId)
            ->select('id', 'property_id', 'customer_id', 'message', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Efficient query for top performing properties (single query with join and grouping)
        $topProperties = Property::where('properties.agent_id', $agentId)  // ⭐ FIXED: Added 'properties.' prefix
            ->select('properties.id', 'properties.title', 'properties.price', 'properties.status')
            ->selectRaw('COUNT(inquiries.id) as inquiries_count')
            ->leftJoin('inquiries', 'properties.id', '=', 'inquiries.property_id')
            ->groupBy('properties.id', 'properties.title', 'properties.price', 'properties.status')
            ->orderByDesc('inquiries_count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'stats' => [
                    'properties' => [
                        'total' => $propertyStats->total ?? 0,
                        'published' => $propertyStats->published ?? 0,
                        'draft' => $propertyStats->draft ?? 0,
                        'sold' => $propertyStats->sold ?? 0,
                        'rented' => $propertyStats->rented ?? 0,
                        'pending_approval' => $propertyStats->pending_approval ?? 0,
                        'approved' => $propertyStats->approved ?? 0,
                        'rejected' => $propertyStats->rejected ?? 0,
                        'this_month' => $propertyStats->this_month ?? 0,
                    ],
                    'inquiries' => [
                        'total' => $inquiryStats->total ?? 0,
                        'new' => $inquiryStats->new ?? 0,
                        'contacted' => $inquiryStats->contacted ?? 0,
                        'closed' => $inquiryStats->closed ?? 0,
                        'recent' => $inquiryStats->recent ?? 0,
                        'this_month' => $inquiryStats->this_month ?? 0,
                    ],
                    'views' => [
                        'total' => $viewStats->total ?? 0,
                        'this_month' => $viewStats->this_month ?? 0,
                        'today' => $viewStats->today ?? 0,
                    ],
                ],
                'recent_properties' => $recentProperties,
                'recent_inquiries' => $latestInquiries,
                'top_properties' => $topProperties,
            ],
        ]);
    }
}