<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * List all appointments for agent
     */
    public function index(Request $request)
    {
        $agentId = auth()->id();
        
        $query = Appointment::with(['property:id,title,address', 'customer:id,name,email,phone'])
            ->where('agent_id', $agentId);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('scheduled_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('scheduled_at', '<=', $request->to_date);
        }

        // Special filters
        if ($request->filter === 'upcoming') {
            $query->upcoming();
        } elseif ($request->filter === 'past') {
            $query->past();
        } elseif ($request->filter === 'today') {
            $query->today();
        }

        $appointments = $query->orderBy('scheduled_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Appointments retrieved successfully',
            'data' => [
                'appointments' => $appointments,
            ],
        ]);
    }

    /**
     * View single appointment
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::with([
                'property:id,title,address,city,price',
                'customer:id,name,email,phone',
                'inquiry'
            ])
            ->where('agent_id', auth()->id())
            ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Appointment details retrieved successfully',
                'data' => [
                    'appointment' => $appointment,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found',
            ], 404);
        }
    }

    /**
     * Create new appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'customer_id' => 'required|exists:users,id',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'type' => 'required|in:visit,call',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'notes' => 'nullable|string|max:1000',
            'location' => 'required_if:type,visit|nullable|string|max:500',
            'phone_number' => 'required_if:type,call|nullable|string|max:20',
        ]);

        try {
            // Verify property belongs to agent
            $property = Property::where('id', $request->property_id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            // Check if customer exists
            $customer = \App\Models\User::where('id', $request->customer_id)
                ->where('role', 'customer')
                ->firstOrFail();

            // Check for conflicting appointments
            $conflict = Appointment::where('agent_id', auth()->id())
                ->where('status', '!=', 'cancelled')
                ->where(function($q) use ($request) {
                    $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
                    $endTime = $scheduledAt->copy()->addMinutes($request->duration_minutes ?? 30);
                    
                    $q->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                      ->orWhere(function($query) use ($scheduledAt, $endTime) {
                          $query->where('scheduled_at', '<=', $scheduledAt)
                                ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) >= ?', [$scheduledAt]);
                      });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have a conflicting appointment at this time',
                ], 400);
            }

            $appointment = Appointment::create([
                'property_id' => $request->property_id,
                'agent_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'inquiry_id' => $request->inquiry_id,
                'type' => $request->type,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes ?? 30,
                'notes' => $request->notes,
                'location' => $request->location,
                'phone_number' => $request->phone_number,
                'status' => 'scheduled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'data' => [
                    'appointment' => $appointment->load(['property', 'customer']),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update appointment
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'sometimes|date|after:now',
            'duration_minutes' => 'sometimes|integer|min:15|max:480',
            'notes' => 'nullable|string|max:1000',
            'agent_notes' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:20',
        ]);

        try {
            $appointment = Appointment::where('agent_id', auth()->id())
                ->findOrFail($id);

            if (!$appointment->canBeRescheduled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This appointment cannot be updated',
                ], 400);
            }

            $appointment->update($request->only([
                'scheduled_at',
                'duration_minutes',
                'notes',
                'agent_notes',
                'location',
                'phone_number',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully',
                'data' => [
                    'appointment' => $appointment->fresh()->load(['property', 'customer']),
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
     * Confirm appointment
     */
    public function confirm($id)
    {
        try {
            $appointment = Appointment::where('agent_id', auth()->id())
                ->findOrFail($id);

            if ($appointment->status !== 'scheduled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only scheduled appointments can be confirmed',
                ], 400);
            }

            $appointment->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment confirmed successfully',
                'data' => [
                    'appointment' => $appointment->fresh(),
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
     * Mark appointment as completed
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'agent_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $appointment = Appointment::where('agent_id', auth()->id())
                ->findOrFail($id);

            if (!in_array($appointment->status, ['scheduled', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only scheduled or confirmed appointments can be completed',
                ], 400);
            }

            $appointment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'agent_notes' => $request->agent_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as completed',
                'data' => [
                    'appointment' => $appointment->fresh(),
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
     * Cancel appointment
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            $appointment = Appointment::where('agent_id', auth()->id())
                ->findOrFail($id);

            if (!$appointment->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This appointment cannot be cancelled',
                ], 400);
            }

            $appointment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => 'agent',
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
                'data' => [
                    'appointment' => $appointment->fresh(),
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
     * Get agent's availability
     */
    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $agentId = auth()->id();
        $date = \Carbon\Carbon::parse($request->date)->startOfDay();

        // Get all appointments for the day
        $appointments = Appointment::where('agent_id', $agentId)
            ->whereDate('scheduled_at', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get(['scheduled_at', 'duration_minutes']);

        // Generate time slots (9 AM to 6 PM, 30-min intervals)
        $timeSlots = [];
        $startTime = $date->copy()->setTime(9, 0);
        $endTime = $date->copy()->setTime(18, 0);

        while ($startTime < $endTime) {
            $slotEnd = $startTime->copy()->addMinutes(30);
            
            // Check if slot is available
            $isAvailable = true;
            foreach ($appointments as $appointment) {
                $appointmentStart = $appointment->scheduled_at;
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration_minutes);
                
                if ($startTime < $appointmentEnd && $slotEnd > $appointmentStart) {
                    $isAvailable = false;
                    break;
                }
            }

            $timeSlots[] = [
                'start_time' => $startTime->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'datetime' => $startTime->toIso8601String(),
                'is_available' => $isAvailable,
            ];

            $startTime->addMinutes(30);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->format('Y-m-d'),
                'time_slots' => $timeSlots,
            ],
        ]);
    }
}