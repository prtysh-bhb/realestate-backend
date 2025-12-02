<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // List all customers
    public function index(Request $request)
    {
        $filters = $request->only('search', 'status');

        $customers = $this->userService->getAllCustomers($filters);

        // Convert items to collection first
        $customersData = collect($customers->items())->map(function($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'city' => $customer->city,
                'avatar' => $customer->avatar_url,
                'status' => $customer->is_active,
                'two_factor_enabled' => $customer->two_factor_enabled,
                'total_inquiries' => $customer->inquiries_count,
                'total_favorites' => $customer->favorites_count,
                'joined' => $customer->created_at->format('m/d/Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                'customers' => $customersData,
                'customers' => $customersData,
                'pagination' => [
                    'total' => $customers->total(),
                    'per_page' => $customers->perPage(),
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                ],
            ],
        ]);
    }

    // View specific customer profile
    public function show($id)
    {
        try {
            $customer = $this->userService->getCustomerById($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer profile retrieved successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'city' => $customer->city,
                        'avatar' => $customer->avatar_url,
                        'bio' => $customer->bio,
                        'address' => $customer->address,
                        'state' => $customer->state,
                        'zipcode' => $customer->zipcode,
                        'status' => $customer->is_active,
                        'two_factor_enabled' => $customer->two_factor_enabled,
                        'total_inquiries' => $customer->inquiries_count,
                        'total_favorites' => $customer->favorites_count,
                        'joined' => $customer->created_at->format('m/d/Y'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}