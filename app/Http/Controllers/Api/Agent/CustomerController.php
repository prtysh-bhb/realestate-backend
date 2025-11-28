<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Get all customers (without pagination) for dropdown
     */
    public function getAllCustomers()
    {
        $customers = User::where('role', 'customer')
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                'customers' => $customers,
            ],
        ]);
    }

    /**
     * Get customers who have inquired on agent's properties (without pagination)
     */
    public function getMyCustomers()
    {
        $agentId = auth()->id();

        // Get unique customers who have inquired on this agent's properties
        $customerIds = Inquiry::where('agent_id', $agentId)
            ->distinct()
            ->pluck('customer_id');

        $customers = User::whereIn('id', $customerIds)
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Your customers retrieved successfully',
            'data' => [
                'customers' => $customers,
            ],
        ]);
    }

    /**
     * Get customer appointments (without pagination)
     */
    public function getCustomerAppointments($customerId)
    {
        $agentId = auth()->id();

        $appointments = \App\Models\Appointment::with(['property:id,title,address'])
            ->where('agent_id', $agentId)
            ->where('customer_id', $customerId)
            ->select('id', 'property_id', 'type', 'scheduled_at', 'status')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer appointments retrieved successfully',
            'data' => [
                'appointments' => $appointments,
                'customer_id' => $customerId,
            ],
        ]);
    }

    /**
     * Get customer inquiries (without pagination)
     */
    public function getCustomerInquiries($customerId)
    {
        $agentId = auth()->id();

        $inquiries = Inquiry::with(['property:id,title,address'])
            ->where('agent_id', $agentId)
            ->where('customer_id', $customerId)
            ->select('id', 'customer_name as name', 'property_id', 'message', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer inquiries retrieved successfully',
            'data' => [
                'inquiries' => $inquiries,
                'customer_id' => $customerId,
            ],
        ]);
    }

    /**
     * Get customer properties (properties customer has shown interest in)
     */
    public function getCustomerProperties($customerId)
    {
        $agentId = auth()->id();

        // Get properties this customer has inquired about
        $propertyIds = Inquiry::where('agent_id', $agentId)
            ->where('customer_id', $customerId)
            ->distinct()
            ->pluck('property_id');

        $properties = \App\Models\Property::whereIn('id', $propertyIds)
            ->where('agent_id', $agentId)
            ->select('id', 'title', 'address', 'city', 'price', 'type')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer properties retrieved successfully',
            'data' => [
                'properties' => $properties,
                'customer_id' => $customerId,
            ],
        ]);
    }

    /**
     * Get customer details with summary
     */
    public function getCustomerDetails($customerId)
    {
        $agentId = auth()->id();

        try {
            $customer = User::where('role', 'customer')
                ->findOrFail($customerId);

            // Get statistics
            $totalInquiries = Inquiry::where('agent_id', $agentId)
                ->where('customer_id', $customerId)
                ->count();

            $totalAppointments = \App\Models\Appointment::where('agent_id', $agentId)
                ->where('customer_id', $customerId)
                ->count();

            $propertiesInterested = Inquiry::where('agent_id', $agentId)
                ->where('customer_id', $customerId)
                ->distinct('property_id')
                ->count('property_id');

            return response()->json([
                'success' => true,
                'message' => 'Customer details retrieved successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                    'summary' => [
                        'total_inquiries' => $totalInquiries,
                        'total_appointments' => $totalAppointments,
                        'properties_interested' => $propertiesInterested,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }
    }
}