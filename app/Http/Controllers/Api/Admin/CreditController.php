<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Credit;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * List all credit packages
     */
    public function index()
    {
        $credits = Credit::orderBy('coins', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $credits,
        ]);
    }

    /**
     * Get single credit package
     */
    public function show($id)
    {
        $credit = Credit::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $credit,
        ]);
    }

    /**
     * Create new credit package
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'coins' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $credit = Credit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Credit package created successfully',
            'data' => $credit,
        ], 201);
    }

    /**
     * Update credit package
     */
    public function update(Request $request, $id)
    {
        $credit = Credit::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:1',
            'coins' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $credit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Credit package updated successfully',
            'data' => $credit,
        ]);
    }

    /**
     * Delete credit package
     */
    public function destroy($id)
    {
        $credit = Credit::findOrFail($id);
        $credit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Credit package deleted successfully',
        ]);
    }
}