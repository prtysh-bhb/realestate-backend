<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // List all agents
    public function index()
    {
        $agents = $this->userService->getAllAgents();

        // Convert items to collection first
        $agentsData = collect($agents->items())->map(function($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
                'city' => $agent->city,
                'avatar' => $agent->avatar_url,
                'company_name' => $agent->company_name,
                'license_number' => $agent->license_number,
                'status' => $agent->is_active ? 'Active' : 'Inactive',
                'total_properties' => $agent->properties_count,
                'joined' => $agent->created_at->format('m/d/Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Agents retrieved successfully',
            'data' => [
                'agents' => $agentsData,
                'pagination' => [
                    'total' => $agents->total(),
                    'per_page' => $agents->perPage(),
                    'current_page' => $agents->currentPage(),
                    'last_page' => $agents->lastPage(),
                ],
            ],
        ]);
    }

    // View specific agent profile
    public function show($id)
    {
        try {
            $agent = $this->userService->getAgentById($id);

            return response()->json([
                'success' => true,
                'message' => 'Agent profile retrieved successfully',
                'data' => [
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'phone' => $agent->phone,
                        'city' => $agent->city,
                        'avatar' => $agent->avatar_url,
                        'bio' => $agent->bio,
                        'company_name' => $agent->company_name,
                        'license_number' => $agent->license_number,
                        'address' => $agent->address,
                        'state' => $agent->state,
                        'zipcode' => $agent->zipcode,
                        'status' => $agent->is_active ? 'Active' : 'Inactive',
                        'total_properties' => $agent->properties_count,
                        'joined' => $agent->created_at->format('m/d/Y'),
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