<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'label',
        'name',
        'value',
        'datatype',
        'description',
    ];

    // Get setting value by name with caching
    public static function get(string $name, $default = null)
    {
        return Cache::remember("app_setting_{$name}", 3600, function () use ($name, $default) {
            $setting = self::where('name', $name)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Set setting value
    public static function set(string $name, $value): bool
    {
        Cache::forget("app_setting_{$name}");
        return self::where('name', $name)->update(['value' => $value]);
    }

    // Get all settings grouped
    public static function getGrouped()
    {
        return self::orderBy('group')->orderBy('label')->get()->groupBy('group');
    }

    // Get settings by group
    public static function getByGroup(string $group)
    {
        return self::where('group', $group)->orderBy('label')->get();
    }
}