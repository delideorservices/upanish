<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'description',
        'value_type',
        'is_editable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_editable' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            // Clear cache when a setting is updated
            Cache::forget('system_settings_' . $setting->setting_key);
            Cache::forget('system_settings_group_' . $setting->setting_group);
            Cache::forget('all_system_settings');
        });

        static::deleted(function ($setting) {
            // Clear cache when a setting is deleted
            Cache::forget('system_settings_' . $setting->setting_key);
            Cache::forget('system_settings_group_' . $setting->setting_group);
            Cache::forget('all_system_settings');
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue($key, $default = null)
    {
        return Cache::remember('system_settings_' . $key, 3600, function () use ($key, $default) {
            $setting = self::where('setting_key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->setting_value, $setting->value_type);
        });
    }

    /**
     * Get all settings in a group.
     */
    public static function getGroup($group)
    {
        return Cache::remember('system_settings_group_' . $group, 3600, function () use ($group) {
            $settings = self::where('setting_group', $group)->get();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->setting_key] = self::castValue($setting->setting_value, $setting->value_type);
            }
            
            return $result;
        });
    }

    /**
     * Get all settings.
     */
    public static function getAllSettings()
    {
        return Cache::remember('all_system_settings', 3600, function () {
            $settings = self::all();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->setting_key] = self::castValue($setting->setting_value, $setting->value_type);
            }
            
            return $result;
        });
    }

    /**
     * Set a setting value.
     */
    public static function setValue($key, $value, $group = 'general', $description = '', $valueType = 'string', $isEditable = true)
    {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) {
            return self::create([
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'setting_group' => $group,
                'description' => $description,
                'value_type' => $valueType,
                'is_editable' => $isEditable,
            ]);
        } else {
            if ($setting->is_editable) {
                $setting->setting_value = (string) $value;
                $setting->save();
            }
            
            return $setting;
        }
    }

    /**
     * Cast a value to its appropriate type.
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }
}