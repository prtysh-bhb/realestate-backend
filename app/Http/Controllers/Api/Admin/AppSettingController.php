<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppSettingController extends Controller
{
    /**
     * GET /admin/settings
     * Get all settings (optionally filtered by group)
     */
    public function index(Request $request)
    {
        $group = $request->query('group');

        if ($group) {
            $settings = AppSetting::getByGroup($group);
        } else {
            $settings = AppSetting::getGrouped();
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * GET /admin/settings/{id}
     * Get single setting
     */
    public function show($id)
    {
        $setting = AppSetting::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Create new setting (rarely used, mostly seeded)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:app_settings,name',
            'value' => 'required|string',
            'datatype' => 'required|in:string,number,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting = AppSetting::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully',
            'data' => $setting,
        ], 201);
    }

    /**
     * Update single setting
     */
    public function update(Request $request, $id)
    {
        $setting = AppSetting::findOrFail($id);

        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        // Clear cache for this setting
        Cache::forget("app_setting_{$setting->name}");

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Update multiple settings at once
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:app_settings,id',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            $setting = AppSetting::find($settingData['id']);
            Cache::forget("app_setting_{$setting->name}");
            $setting->update(['value' => $settingData['value']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Delete setting
     */
    public function destroy($id)
    {
        $setting = AppSetting::findOrFail($id);
        Cache::forget("app_setting_{$setting->name}");
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }
}