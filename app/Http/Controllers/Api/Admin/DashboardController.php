<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\PropertyView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // User Statistics
        $userStats = User::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN role = "agent" THEN 1 ELSE 0 END) as agents'),
                DB::raw('SUM(CASE WHEN role = "customer" THEN 1 ELSE 0 END) as customers'),
                DB::raw('SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admins'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users'),
                DB::raw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as deactivated_users'),
                DB::raw('SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month')
            )
            ->first();

        // Property Statistics - ONLY EXISTING COLUMNS
        $propertyStats = Property::select(
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

        // Inquiry Statistics
        $inquiryStats = Inquiry::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new'),
                DB::raw('SUM(CASE WHEN status = "contacted" THEN 1 ELSE 0 END) as contacted'),
                DB::raw('SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed'),
                DB::raw('SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent'),
                DB::raw('SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month')
            )
            ->first();

        // Property Views Statistics
        $viewStats = PropertyView::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN MONTH(viewed_at) = MONTH(NOW()) AND YEAR(viewed_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month'),
                DB::raw('SUM(CASE WHEN DATE(viewed_at) = CURDATE() THEN 1 ELSE 0 END) as today')
            )
            ->first();

        // Recent Users
        $recentUsers = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Properties
        $recentProperties = Property::with('agent:id,name,email')
            ->select('id', 'title', 'price', 'status', 'approval_status', 'agent_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending Approvals
        $pendingApprovals = Property::with('agent:id,name,email')
            ->where('approval_status', 'pending')
            ->select('id', 'title', 'price', 'agent_id', 'created_at')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Top Agents by Properties
        $topAgentsByProperties = User::where('role', 'agent')
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('COUNT(properties.id) as properties_count')
            ->leftJoin('properties', 'users.id', '=', 'properties.agent_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('properties_count')
            ->limit(5)
            ->get();

        // Top Agents by Inquiries
        $topAgentsByInquiries = User::where('role', 'agent')
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('COUNT(inquiries.id) as inquiries_count')
            ->leftJoin('inquiries', 'users.id', '=', 'inquiries.agent_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('inquiries_count')
            ->limit(5)
            ->get();

        // Properties by Type
        $propertiesByType = Property::select('type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard data retrieved successfully',
            'data' => [
                'stats' => [
                    'users' => [
                        'total' => $userStats->total ?? 0,
                        'agents' => $userStats->agents ?? 0,
                        'customers' => $userStats->customers ?? 0,
                        'admins' => $userStats->admins ?? 0,
                        'active' => $userStats->active_users ?? 0,
                        'deactivated' => $userStats->deactivated_users ?? 0,
                        'this_month' => $userStats->this_month ?? 0,
                    ],
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
                'recent_users' => $recentUsers,
                'recent_properties' => $recentProperties,
                'pending_approvals' => $pendingApprovals,
                'top_agents_by_properties' => $topAgentsByProperties,
                'top_agents_by_inquiries' => $topAgentsByInquiries,
                'properties_by_type' => $propertiesByType,
            ],
        ]);
    }
}