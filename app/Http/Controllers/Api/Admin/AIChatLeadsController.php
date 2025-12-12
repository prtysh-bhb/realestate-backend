<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLead;
use Illuminate\Http\Request;

class AIChatLeadsController extends Controller
{
    /**
     * GET /api/admin/ai/chat/leads
     * View all chat leads
     */
    public function index(Request $request)
    {
        $query = AiChatLead::with(['conversation', 'user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by lead score
        if ($request->has('min_score')) {
            $query->where('lead_score', '>=', $request->min_score);
        }

        // Sort by lead score or created date
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    /**
     * GET /api/admin/ai/chat/leads/{id}
     * View specific lead
     */
    public function show($id)
    {
        $lead = AiChatLead::with(['conversation', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    /**
     * PUT /api/admin/ai/chat/leads/{id}/status
     * Update lead status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $lead = AiChatLead::findOrFail($id);
        $lead->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated',
            'data' => $lead,
        ]);
    }
}