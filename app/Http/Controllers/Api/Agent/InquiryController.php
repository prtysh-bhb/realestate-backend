<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Services\InquiryService;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    protected $inquiryService;

    public function __construct(InquiryService $inquiryService)
    {
        $this->inquiryService = $inquiryService;
    }

    // List agent's inquiries
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $stage = $request->input('stage');
        
        $inquiries = $this->inquiryService->getAgentInquiries(
            $request->user()->id,
            $status,
            $stage
        );

        return response()->json([
            'success' => true,
            'message' => 'Inquiries retrieved successfully',
            'data' => [
                'inquiries' => $inquiries->items(),
                'pagination' => [
                    'total' => $inquiries->total(),
                    'per_page' => $inquiries->perPage(),
                    'current_page' => $inquiries->currentPage(),
                    'last_page' => $inquiries->lastPage(),
                ],
            ],
        ]);
    }

    // View specific inquiry
    public function show(Request $request, $id)
    {
        try {
            $inquiry = $this->inquiryService->getInquiryById(
                $id,
                $request->user()->id,
                'agent'
            );

            return response()->json([
                'success' => true,
                'message' => 'Inquiry details retrieved successfully',
                'data' => [
                    'inquiry' => $inquiry,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // Update inquiry status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,contacted,interested,not_interested,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $inquiry = $this->inquiryService->updateInquiryStatus(
                $id,
                $request->user()->id,
                $request->status,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Inquiry status updated successfully',
                'data' => [
                    'inquiry' => $inquiry,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update lead stage
     */
    public function updateStage(Request $request, $id)
    {
        $request->validate([
            'stage' => 'required|in:new,contacted,qualified,negotiation,closed_won,closed_lost',
            'note' => 'nullable|string',
        ]);

        $inquiry = Inquiry::with(['customer:id,name,email,avatar', 'property:id,title,location'])
            ->whereHas('property', function($q) {
                $q->where('agent_id', auth()->id());
            })
            ->findOrFail($id);

        $oldStage = $inquiry->stage;
        $newStage = $request->stage;

        // Add to history
        $history = $inquiry->history ?? [];
        $history[] = [
            'from_stage' => $oldStage,
            'to_stage' => $newStage,
            'note' => $request->note,
            'changed_by' => auth()->user()->name,
            'changed_at' => now()->toDateTimeString(),
        ];

        $inquiry->update([
            'stage' => $newStage,
            'history' => $history,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead stage updated successfully',
            'data' => ['inquiry' => $inquiry->fresh(['customer:id,name,email,avatar', 'property:id,title,location'])]
        ]);
    }

    /**
     * Add notes to inquiry
     */
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $inquiry = Inquiry::with(['customer:id,name,email,avatar', 'property:id,title,location'])
            ->whereHas('property', function($q) {
                $q->where('agent_id', auth()->id());
            })
            ->findOrFail($id);

        $currentNotes = $inquiry->notes ? $inquiry->notes . "\n\n" : '';
        $newNote = "--- " . now()->format('Y-m-d H:i:s') . " by " . auth()->user()->name . " ---\n" . $request->note;
        
        $inquiry->update([
            'notes' => $currentNotes . $newNote
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully',
            'data' => ['inquiry' => $inquiry->fresh(['customer:id,name,email,avatar', 'property:id,title,location'])]
        ]);
    }

    /**
     * Get lead history
     */
    public function history($id)
    {
        $inquiry = Inquiry::with(['customer:id,name,email,avatar', 'property:id,title,location'])
            ->whereHas('property', function($q) {
                $q->where('agent_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'history' => $inquiry->history ?? [],
                'inquiry' => $inquiry
            ]
        ]);
    }
}