<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * List all reminders
     */
    public function index(Request $request)
    {
        $agentId = auth()->id();
        
        $query = Reminder::with([
            'customer:id,name,email,phone',
            'property:id,title,address',
            'inquiry:id,message',
            'appointment:id,type,scheduled_at'
        ])
        ->where('agent_id', $agentId);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Special filters
        if ($request->filter === 'overdue') {
            $query->overdue();
        } elseif ($request->filter === 'today') {
            $query->dueToday();
        } elseif ($request->filter === 'upcoming') {
            $query->upcoming($request->days ?? 7);
        } elseif ($request->filter === 'pending') {
            $query->pending();
        }

        $reminders = $query->orderBy('remind_at', 'asc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Reminders retrieved successfully',
            'data' => [
                'reminders' => $reminders,
            ],
        ]);
    }

    /**
     * View single reminder
     */
    public function show($id)
    {
        try {
            $reminder = Reminder::with([
                'customer:id,name,email,phone',
                'property:id,title,address',
                'inquiry',
                'appointment'
            ])
            ->where('agent_id', auth()->id())
            ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'reminder' => $reminder,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder not found',
            ], 404);
        }
    }

    /**
     * Create new reminder
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:inquiry_followup,appointment_followup,general,document_pending,payment_followup',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'remind_at' => 'required|date|after_or_equal:now',
            'customer_id' => 'nullable|exists:users,id',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'property_id' => 'nullable|exists:properties,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $reminder = Reminder::create([
                'agent_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'inquiry_id' => $request->inquiry_id,
                'property_id' => $request->property_id,
                'appointment_id' => $request->appointment_id,
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'priority' => $request->priority ?? 'medium',
                'remind_at' => $request->remind_at,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder created successfully',
                'data' => [
                    'reminder' => $reminder->load(['customer', 'property', 'inquiry', 'appointment']),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reminder: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update reminder
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'sometimes|in:inquiry_followup,appointment_followup,general,document_pending,payment_followup',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'remind_at' => 'sometimes|date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $reminder = Reminder::where('agent_id', auth()->id())
                ->findOrFail($id);

            if ($reminder->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed reminders cannot be edited',
                ], 400);
            }

            $reminder->update($request->only([
                'title',
                'description',
                'type',
                'priority',
                'remind_at',
                'notes',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Reminder updated successfully',
                'data' => [
                    'reminder' => $reminder->fresh()->load(['customer', 'property']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark reminder as completed
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $reminder = Reminder::where('agent_id', auth()->id())
                ->findOrFail($id);

            if ($reminder->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder is already completed',
                ], 400);
            }

            $reminder->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $request->notes ?? $reminder->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder marked as completed',
                'data' => [
                    'reminder' => $reminder->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Snooze reminder
     */
    public function snooze(Request $request, $id)
    {
        $request->validate([
            'snooze_until' => 'required|date|after:now',
        ]);

        try {
            $reminder = Reminder::where('agent_id', auth()->id())
                ->findOrFail($id);

            if ($reminder->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed reminders cannot be snoozed',
                ], 400);
            }

            $reminder->update([
                'status' => 'snoozed',
                'snoozed_until' => $request->snooze_until,
                'remind_at' => $request->snooze_until,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder snoozed successfully',
                'data' => [
                    'reminder' => $reminder->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel reminder
     */
    public function cancel($id)
    {
        try {
            $reminder = Reminder::where('agent_id', auth()->id())
                ->findOrFail($id);

            if ($reminder->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed reminders cannot be cancelled',
                ], 400);
            }

            $reminder->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder cancelled successfully',
                'data' => [
                    'reminder' => $reminder->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete reminder
     */
    public function destroy($id)
    {
        try {
            $reminder = Reminder::where('agent_id', auth()->id())
                ->findOrFail($id);

            $reminder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reminder deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard summary
     */
    public function summary()
    {
        $agentId = auth()->id();

        $summary = [
            'overdue' => Reminder::where('agent_id', $agentId)->overdue()->count(),
            'due_today' => Reminder::where('agent_id', $agentId)->dueToday()->count(),
            'upcoming_7_days' => Reminder::where('agent_id', $agentId)->upcoming(7)->count(),
            'pending' => Reminder::where('agent_id', $agentId)->pending()->count(),
            'completed_today' => Reminder::where('agent_id', $agentId)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
        ];

        // Priority breakdown
        $priorityBreakdown = Reminder::where('agent_id', $agentId)
            ->where('status', 'pending')
            ->select('priority', \DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Type breakdown
        $typeBreakdown = Reminder::where('agent_id', $agentId)
            ->where('status', 'pending')
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'by_priority' => $priorityBreakdown,
                'by_type' => $typeBreakdown,
            ],
        ]);
    }

    /**
     * Quick create reminder from inquiry
     */
    public function createFromInquiry(Request $request, $inquiryId)
    {
        $request->validate([
            'remind_at' => 'required|date|after:now',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $inquiry = \App\Models\Inquiry::where('agent_id', auth()->id())
                ->with(['property:id,title', 'customer:id,name'])
                ->findOrFail($inquiryId);

            $reminder = Reminder::create([
                'agent_id' => auth()->id(),
                'customer_id' => $inquiry->customer_id,
                'inquiry_id' => $inquiry->id,
                'property_id' => $inquiry->property_id,
                'title' => "Follow-up: {$inquiry->customer->name} - {$inquiry->property->title}",
                'description' => "Follow-up on inquiry from {$inquiry->created_at->format('M d, Y')}",
                'type' => 'inquiry_followup',
                'priority' => $request->priority ?? 'medium',
                'remind_at' => $request->remind_at,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Follow-up reminder created successfully',
                'data' => [
                    'reminder' => $reminder->load(['customer', 'property', 'inquiry']),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick create reminder from appointment
     */
    public function createFromAppointment(Request $request, $appointmentId)
    {
        $request->validate([
            'remind_at' => 'required|date|after:now',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $appointment = \App\Models\Appointment::where('agent_id', auth()->id())
                ->with(['property:id,title', 'customer:id,name'])
                ->findOrFail($appointmentId);

            $reminder = Reminder::create([
                'agent_id' => auth()->id(),
                'customer_id' => $appointment->customer_id,
                'appointment_id' => $appointment->id,
                'property_id' => $appointment->property_id,
                'title' => "Follow-up: {$appointment->type} with {$appointment->customer->name}",
                'description' => "Follow-up after {$appointment->type} scheduled for {$appointment->scheduled_at->format('M d, Y')}",
                'type' => 'appointment_followup',
                'priority' => $request->priority ?? 'medium',
                'remind_at' => $request->remind_at,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Follow-up reminder created successfully',
                'data' => [
                    'reminder' => $reminder->load(['customer', 'property', 'appointment']),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}