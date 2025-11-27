<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'avatar_url' => $user->avatar_url,
                    'bio' => $user->bio,
                    'company_name' => $user->company_name,
                    'license_number' => $user->license_number,
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'zipcode' => $user->zipcode,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ]
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:70|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'zipcode' => 'nullable|string|max:10',
        ];

        // Add agent-specific fields if user is agent
        if ($user->isAgent()) {
            $rules['company_name'] = 'nullable|string|max:100';
            $rules['license_number'] = 'nullable|string|max:50';
        }

        $validated = $request->validate($rules);

        try {
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'avatar_url' => $user->avatar_url,
                        'bio' => $user->bio,
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
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|max:50',
            'new_password' => ['required', 'confirmed', 'max:50', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB
        ]);

        $user = $request->user();

        try {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');

            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'avatar' => $path,
                    'avatar_url' => $user->avatar_url,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if (!$user->avatar) {
            return response()->json([
                'success' => false,
                'message' => 'No avatar to delete',
            ], 404);
        }

        try {
            // Delete from storage
            Storage::disk('public')->delete($user->avatar);

            // Update database
            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
        ]);

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
                'errors' => [
                    'password' => ['The password is incorrect.']
                ]
            ], 422);
        }

        try {
            // Delete avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->tokens()->delete();

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
            ], 500);
        }
    }
}