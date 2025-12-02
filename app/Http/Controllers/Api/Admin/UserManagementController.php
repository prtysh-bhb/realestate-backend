<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserManagementController extends Controller
{
    /**
     * Deactivate user account
     */
    public function deactivate(Request $request, $userId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $user = User::findOrFail($userId);

            // Cannot deactivate admin
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate admin accounts',
                ], 403);
            }

            // Cannot deactivate self
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate your own account',
                ], 403);
            }

            $user->update([
                'is_active' => 0,
                'deactivation_reason' => $request->reason,
                'deactivated_at' => now(),
            ]);

            // Revoke all tokens
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'User account deactivated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_active' => $user->is_active,
                        'deactivated_at' => $user->deactivated_at,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate user account
     */
    public function activate($userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'is_active' => 1,
            'deactivation_reason' => null,
            'deactivated_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User account activated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                ]
            ]
        ]);
    }

    /**
     * Get user status
     */
    public function status($userId)
    {
        $user = User::findOrFail($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'deactivation_reason' => $user->deactivation_reason,
                    'deactivated_at' => $user->deactivated_at,
                ]
            ]
        ]);
    }

    /**
     * List all users with filter
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Update agent/user profile by admin
     */
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'state' => 'sometimes|nullable|string|max:100',
            'zipcode' => 'sometimes|nullable|string|max:20',
        ];

        // Add agent-specific fields
        if ($user->role === 'agent') {
            $rules['company_name'] = 'sometimes|nullable|string|max:255';
            $rules['license_number'] = 'sometimes|nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        try {
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'company_name' => $user->company_name,
                        'license_number' => $user->license_number,
                        'address' => $user->address,
                        'city' => $user->city,
                        'state' => $user->state,
                        'zipcode' => $user->zipcode,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export users to Excel
     */
    public function export(Request $request)
    {
        $role = $request->input('role'); // agent, customer, admin
        $isActive = $request->input('is_active'); // 0, 1

        $filename = 'users_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new UsersExport($role, $isActive), $filename);
    }

    /**
     * Show Agent Details
     */
    public function showAgent($id)
    {
        $agent = User::where('id', $id)
            ->where('role', 'agent')
            ->withCount('properties')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
                'location' => $agent->location,
                'avatar' => $agent->avatar_url,
                'bio' => $agent->bio,
                'company_name' => $agent->company_name,
                'license_number' => $agent->license_number,
                'address' => $agent->address,
                'city' => $agent->city,
                'state' => $agent->state,
                'zipcode' => $agent->zipcode,
                'status' => $agent->is_active ? 'Active' : 'Inactive',
                'total_properties' => $agent->properties_count,
                'joined' => $agent->created_at->format('m/d/Y'),
            ],
        ]);
    }

    /**
     * Show Customer Details
     */
    public function showCustomer($id)
    {
        $customer = User::where('id', $id)
            ->where('role', 'customer')
            ->withCount(['inquiries', 'favorites'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
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
        ]);
    }

    /**
     * Update Profile (with avatar upload)
     */
    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'location' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string|max:1000',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                \Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh(),
        ]);
    }
}