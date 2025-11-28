<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use App\Events\LeadAssignedEvent;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function assignLead(Request $request, $id)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        $inquiry = Inquiry::findOrFail($id);
        $agent = User::where('id', $request->agent_id)->where('role', 'agent')->firstOrFail();
        
        $inquiry->update(['agent_id' => $request->agent_id]);

        // Fire event
        event(new LeadAssignedEvent($inquiry, $agent));

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned successfully',
            'data' => $inquiry->fresh(['agent', 'customer', 'property']),
        ]);
    }
}