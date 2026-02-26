<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseApiController
{
    protected string $version = 'v1';

    /**
     * Display a listing of public settings
     */
    public function index(Request $request): JsonResponse
    {
        // Get only public and active settings
        $query = Setting::where('is_public', true)
            ->where('is_active', true);

        // Filter by group if provided
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $settings = $query->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $this->transformValue($setting)];
            });

        // Add timezones to the response
        $timezones = getTimezoneOptions();
        if (!empty($timezones)) {
            $settings['timezones'] = $timezones;
        }

        // Add minimum password length
        $settings['min_password_length'] = password_min_length();

        $settings['assets_url'] = app_url();

        // Add mobile socket settings
        $settings['socket_enabled'] = socket_enabled();
        $settings['socket_server_url'] = socket_server_url();
        $settings['socket_server_port'] = socket_server_port();
        $settings['socket_reconnection_attempts'] = socket_reconnection_attempts();
        $settings['socket_reconnection_delay'] = socket_reconnection_delay();
        $settings['socket_reconnection_delay_max'] = socket_reconnection_delay_max();
        $settings['socket_timeout'] = socket_timeout();

        return $this->successResponse($settings, 'Settings retrieved successfully');
    }

    /**
     * Display the specified setting
     */
    public function show(Request $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)
            ->where('is_public', true)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return $this->notFoundResponse('Setting not found or not publicly accessible');
        }

        return $this->successResponse([
            'key' => $setting->key,
            'value' => $this->transformValue($setting),
            'name' => $setting->name,
            'description' => $setting->description,
        ], 'Setting retrieved successfully');
    }

    /**
     * Transform setting value based on type
     */
    protected function transformValue(Setting $setting): mixed
    {
        return match ($setting->type) {
            'boolean' => $setting->value === '1' || $setting->value === 1 || $setting->value === true,
            'number' => is_numeric($setting->value) ? (int) $setting->value : $setting->value,
            'json' => json_decode($setting->value, true) ?? $setting->value,
            default => $setting->value,
        };
    }
}
