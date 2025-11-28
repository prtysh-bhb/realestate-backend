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
    public function index()
    {
        $customers = $this->userService->getAllCustomers();

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                'customers' => $customers->items()->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'location' => $customer->location,
                        'avatar' => $customer->avatar_url,
                        'status' => $customer->is_active ? 'Active' : 'Inactive',
                        'total_inquiries' => $customer->inquiries_count,
                        'total_favorites' => $customer->favorites_count,
                        'joined' => $customer->created_at->format('m/d/Y'),
                    ];
                }),
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
                        'location' => $customer->location,
                        'avatar' => $customer->avatar_url,
                        'bio' => $customer->bio,
                        'address' => $customer->address,
                        'city' => $customer->city,
                        'state' => $customer->state,
                        'zipcode' => $customer->zipcode,
                        'status' => $customer->is_active ? 'Active' : 'Inactive',
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