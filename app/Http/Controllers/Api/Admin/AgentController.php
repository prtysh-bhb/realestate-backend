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

        return response()->json([
            'success' => true,
            'message' => 'Agents retrieved successfully',
            'data' => [
                'agents' => $agents->items(),
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
                    'agent' => $agent,
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