<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('formatUserDateTime')) {
    /**
     * Format datetime in user's timezone for display
     * Database stores in UTC, this converts to user's timezone from settings
     * 
     * @param mixed $datetime UTC datetime from database
     * @param string|null $format Display format (defaults to setting)
     * @return string Formatted datetime in user's timezone
     */
    function formatUserDateTime($datetime, ?string $format = null): string
    {
        if (!$datetime) {
            return '-';
        }

        // Parse as UTC if it's a string (database datetimes are UTC)
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime, 'UTC');

        // Get user's timezone from settings
        $userTimezone = Auth::check() && Auth::user()->timezone
            ? Auth::user()->timezone
            : timezone_setting();

        // Get format from settings if not provided
        $format = $format ?? datetime_format_setting();

        return $carbon->setTimezone($userTimezone)->format($format);
    }
}

if (!function_exists('formatUserDate')) {
    /**
     * Format date in user's timezone for display
     * 
     * @param mixed $date UTC date from database
     * @param string|null $format Display format (defaults to setting)
     * @return string Formatted date in user's timezone
     */
    function formatUserDate($date, ?string $format = null): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date, 'UTC');

        $userTimezone = Auth::check() && Auth::user()->timezone
            ? Auth::user()->timezone
            : timezone_setting();

        $format = $format ?? date_format_setting();

        return $carbon->setTimezone($userTimezone)->format($format);
    }
}

if (!function_exists('getTimezoneOptions')) {
    /**
     * Get timezone options for select dropdowns
     */
    function getTimezoneOptions(): array
    {
        return [
            // UTC
            'UTC' => 'UTC',

            // Americas
            'America/New_York' => 'America/New_York (Eastern Time)',
            'America/Chicago' => 'America/Chicago (Central Time)',
            'America/Denver' => 'America/Denver (Mountain Time)',
            'America/Los_Angeles' => 'America/Los_Angeles (Pacific Time)',
            'America/Anchorage' => 'America/Anchorage (Alaska Time)',
            'Pacific/Honolulu' => 'Pacific/Honolulu (Hawaii Time)',
            'America/Toronto' => 'America/Toronto',
            'America/Vancouver' => 'America/Vancouver',
            'America/Mexico_City' => 'America/Mexico_City',
            'America/Sao_Paulo' => 'America/Sao_Paulo',
            'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',

            // Europe
            'Europe/London' => 'Europe/London (GMT/BST)',
            'Europe/Paris' => 'Europe/Paris (CET/CEST)',
            'Europe/Berlin' => 'Europe/Berlin (CET/CEST)',
            'Europe/Rome' => 'Europe/Rome (CET/CEST)',
            'Europe/Madrid' => 'Europe/Madrid (CET/CEST)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
            'Europe/Brussels' => 'Europe/Brussels (CET/CEST)',
            'Europe/Vienna' => 'Europe/Vienna (CET/CEST)',
            'Europe/Zurich' => 'Europe/Zurich (CET/CEST)',
            'Europe/Stockholm' => 'Europe/Stockholm (CET/CEST)',
            'Europe/Oslo' => 'Europe/Oslo (CET/CEST)',
            'Europe/Copenhagen' => 'Europe/Copenhagen (CET/CEST)',
            'Europe/Helsinki' => 'Europe/Helsinki (EET/EEST)',
            'Europe/Warsaw' => 'Europe/Warsaw (CET/CEST)',
            'Europe/Prague' => 'Europe/Prague (CET/CEST)',
            'Europe/Budapest' => 'Europe/Budapest (CET/CEST)',
            'Europe/Bucharest' => 'Europe/Bucharest (EET/EEST)',
            'Europe/Athens' => 'Europe/Athens (EET/EEST)',
            'Europe/Istanbul' => 'Europe/Istanbul (TRT)',
            'Europe/Moscow' => 'Europe/Moscow (MSK)',

            // Asia
            'Asia/Tokyo' => 'Asia/Tokyo (JST)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST)',
            'Asia/Hong_Kong' => 'Asia/Hong_Kong (HKT)',
            'Asia/Singapore' => 'Asia/Singapore (SGT)',
            'Asia/Seoul' => 'Asia/Seoul (KST)',
            'Asia/Taipei' => 'Asia/Taipei (CST)',
            'Asia/Bangkok' => 'Asia/Bangkok (ICT)',
            'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
            'Asia/Manila' => 'Asia/Manila (PHT)',
            'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur (MYT)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST)',
            'Asia/Dubai' => 'Asia/Dubai (GST)',
            'Asia/Karachi' => 'Asia/Karachi (PKT)',
            'Asia/Dhaka' => 'Asia/Dhaka (BST)',

            // Australia & Pacific
            'Australia/Sydney' => 'Australia/Sydney (AEST/AEDT)',
            'Australia/Melbourne' => 'Australia/Melbourne (AEST/AEDT)',
            'Australia/Brisbane' => 'Australia/Brisbane (AEST)',
            'Australia/Perth' => 'Australia/Perth (AWST)',
            'Australia/Adelaide' => 'Australia/Adelaide (ACST/ACDT)',
            'Pacific/Auckland' => 'Pacific/Auckland (NZST/NZDT)',
            'Pacific/Fiji' => 'Pacific/Fiji (FJT)',

            // Africa
            'Africa/Cairo' => 'Africa/Cairo (EET)',
            'Africa/Johannesburg' => 'Africa/Johannesburg (SAST)',
            'Africa/Lagos' => 'Africa/Lagos (WAT)',
            'Africa/Nairobi' => 'Africa/Nairobi (EAT)',
            'Africa/Casablanca' => 'Africa/Casablanca (WET)',
        ];
    }
}

if (!function_exists('getLanguageOptions')) {
    /**
     * Get language options for select dropdowns
     */
    function getLanguageOptions(): array
    {
        return [
            'en' => 'English',
        ];
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            $carbon = $date instanceof Carbon ? $date : Carbon::parse($date, 'UTC');

            $userTimezone = Auth::check() && Auth::user()->timezone
                ? Auth::user()->timezone
                : timezone_setting();

            return $carbon->setTimezone($userTimezone)->diffForHumans();
        } catch (\Exception $e) {
            return '-';
        }
    }
}
