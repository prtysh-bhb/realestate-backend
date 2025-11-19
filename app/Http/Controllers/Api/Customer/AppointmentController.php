<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * List customer's appointments
     */
    public function index(Request $request)
    {
        $customerId = auth()->id();
        
        $query = Appointment::with(['property:id,title,address,agent_id', 'property.agent:id,name,email,phone', 'agent:id,name,email,phone'])
            ->where('customer_id', $customerId);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filter === 'upcoming') {
            $query->upcoming();
        } elseif ($request->filter === 'past') {
            $query->past();
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
                'property:id,title,address,city,price,agent_id',
                'agent:id,name,email,phone'
            ])
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

            return response()->json([
                'success' => true,
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
     * Request appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'type' => 'required|in:visit,call',
            'scheduled_at' => 'required|date|after:now',
            'customer_notes' => 'nullable|string|max:500',
            'phone_number' => 'required_if:type,call|nullable|string|max:20',
        ]);

        try {
            $property = Property::with('agent:id')->findOrFail($request->property_id);

            // Check if property is available
            if ($property->status !== 'published' || $property->approval_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'This property is not available for appointments',
                ], 400);
            }

            $appointment = Appointment::create([
                'property_id' => $request->property_id,
                'agent_id' => $property->agent_id,
                'customer_id' => auth()->id(),
                'type' => $request->type,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => 30,
                'customer_notes' => $request->customer_notes,
                'phone_number' => $request->phone_number,
                'location' => $property->address,
                'status' => 'scheduled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment requested successfully. Agent will confirm shortly.',
                'data' => [
                    'appointment' => $appointment->load(['property', 'agent']),
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
     * Cancel appointment
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            $appointment = Appointment::where('customer_id', auth()->id())
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
                'cancelled_by' => 'customer',
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
     * Get agent availability for a property
     */
    public function checkAvailability(Request $request, $propertyId)
    {
        $request->validate([
            'date' => 'required|date|after:today',
        ]);

        try {
            $property = Property::findOrFail($propertyId);
            $agentId = $property->agent_id;
            $date = \Carbon\Carbon::parse($request->date)->startOfDay();

            // Get agent's appointments for the day
            $appointments = Appointment::where('agent_id', $agentId)
                ->whereDate('scheduled_at', $date)
                ->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_at')
                ->get(['scheduled_at', 'duration_minutes']);

            // Generate available time slots
            $timeSlots = [];
            $startTime = $date->copy()->setTime(9, 0);
            $endTime = $date->copy()->setTime(18, 0);

            while ($startTime < $endTime) {
                $slotEnd = $startTime->copy()->addMinutes(30);
                
                $isAvailable = true;
                foreach ($appointments as $appointment) {
                    $appointmentStart = $appointment->scheduled_at;
                    $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration_minutes);
                    
                    if ($startTime < $appointmentEnd && $slotEnd > $appointmentStart) {
                        $isAvailable = false;
                        break;
                    }
                }

                if ($isAvailable) {
                    $timeSlots[] = [
                        'time' => $startTime->format('H:i'),
                        'datetime' => $startTime->toIso8601String(),
                    ];
                }

                $startTime->addMinutes(30);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date->format('Y-m-d'),
                    'available_slots' => $timeSlots,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}