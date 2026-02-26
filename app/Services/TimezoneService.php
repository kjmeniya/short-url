<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TimezoneService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get the user's timezone or fallback to system timezone
     */
    public function getUserTimezone(): string
    {
        // Try to get user's timezone first
        if (Auth::check() && Auth::user()->timezone) {
            return Auth::user()->timezone;
        }

        // Fallback to system timezone setting
        return timezone_setting();
    }

    /**
     * Get default date format from settings
     */
    public function getDefaultDateFormat(): string
    {
        return date_format_setting();
    }

    /**
     * Get default datetime format from settings
     */
    public function getDefaultDateTimeFormat(): string
    {
        return datetime_format_setting();
    }

    /**
     * Convert a date to user's timezone and format it
     */
    public function formatDateForUser($date, ?string $format = null): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $userTimezone = $this->getUserTimezone();
        $format = $format ?? $this->getDefaultDateFormat();
        
        return $carbon->setTimezone($userTimezone)->format($format);
    }

    /**
     * Convert a datetime to user's timezone and format it
     */
    public function formatDateTimeForUser($datetime, ?string $format = null): string
    {
        if (!$datetime) {
            return '-';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $userTimezone = $this->getUserTimezone();
        $format = $format ?? $this->getDefaultDateTimeFormat();
        
        return $carbon->setTimezone($userTimezone)->format($format);
    }

    /**
     * Get relative time (e.g., "2 hours ago") in user's timezone
     */
    public function formatRelativeTimeForUser($datetime): string
    {
        if (!$datetime) {
            return '-';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $userTimezone = $this->getUserTimezone();
        
        return $carbon->setTimezone($userTimezone)->diffForHumans();
    }

    /**
     * Get timezone display name for user
     */
    public function getUserTimezoneDisplay(): string
    {
        $timezone = $this->getUserTimezone();
        $carbon = Carbon::now($timezone);
        
        return $timezone . ' (' . $carbon->format('T') . ')';
    }

    /**
     * Convert date from user timezone to UTC for database storage
     */
    public function convertToUtc($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$date) {
            return '';
        }

        $userTimezone = $this->getUserTimezone();
        $carbon = Carbon::createFromFormat($format, $date, $userTimezone);
        
        return $carbon->utc()->format('Y-m-d H:i:s');
    }

    /**
     * Convert a UTC date to user's timezone
     */
    public function convertToUserTimezone($date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $userTimezone = $this->getUserTimezone();
        
        return $carbon->setTimezone($userTimezone);
    }

    /**
     * Format date with tooltip showing full datetime
     */
    public function formatDateWithTooltip($date, ?string $displayFormat = null, ?string $tooltipFormat = null): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $userTimezone = $this->getUserTimezone();
        $carbonInUserTz = $carbon->setTimezone($userTimezone);
        
        $displayFormat = $displayFormat ?? $this->getDefaultDateFormat();
        $tooltipFormat = $tooltipFormat ?? ($this->getDefaultDateTimeFormat() . ' T');
        
        $displayDate = $carbonInUserTz->format($displayFormat);
        $tooltipDate = $carbonInUserTz->format($tooltipFormat);
        
        return '<span title="' . htmlspecialchars($tooltipDate) . '" data-bs-toggle="tooltip">' . $displayDate . '</span>';
    }

    /**
     * Get common date formats for admin interface
     */
    public function getCommonDateFormats(): array
    {
        return [
            'date_short' => $this->getDefaultDateFormat(),
            'date_long' => 'F j, Y',
            'datetime_short' => $this->getDefaultDateTimeFormat(),
            'datetime_long' => 'F j, Y g:i:s A',
            'time_only' => 'g:i A',
            'iso_date' => 'Y-m-d',
            'iso_datetime' => 'Y-m-d H:i:s',
        ];
    }

    /**
     * Format date with specific format key
     */
    public function formatWithKey($date, string $formatKey): string
    {
        $formats = $this->getCommonDateFormats();
        $format = $formats[$formatKey] ?? $formats['date_short'];
        
        return $this->formatDateTimeForUser($date, $format);
    }

    /**
     * Get available date format options for settings
     */
    public function getDateFormatOptions(): array
    {
        $sampleDate = Carbon::create(2024, 1, 15, 14, 30, 45);
        
        return [
            'M d, Y' => $sampleDate->format('M d, Y') . ' (M d, Y)',
            'F j, Y' => $sampleDate->format('F j, Y') . ' (F j, Y)',
            'd/m/Y' => $sampleDate->format('d/m/Y') . ' (d/m/Y)',
            'm/d/Y' => $sampleDate->format('m/d/Y') . ' (m/d/Y)',
            'Y-m-d' => $sampleDate->format('Y-m-d') . ' (Y-m-d)',
            'd-m-Y' => $sampleDate->format('d-m-Y') . ' (d-m-Y)',
            'd.m.Y' => $sampleDate->format('d.m.Y') . ' (d.m.Y)',
            'j M Y' => $sampleDate->format('j M Y') . ' (j M Y)',
            'l, F j, Y' => $sampleDate->format('l, F j, Y') . ' (l, F j, Y)',
        ];
    }

    /**
     * Get available datetime format options for settings
     */
    public function getDateTimeFormatOptions(): array
    {
        $sampleDate = Carbon::create(2024, 1, 15, 14, 30, 45);
        
        return [
            'M d, Y g:i A' => $sampleDate->format('M d, Y g:i A') . ' (M d, Y g:i A)',
            'F j, Y g:i A' => $sampleDate->format('F j, Y g:i A') . ' (F j, Y g:i A)',
            'd/m/Y H:i' => $sampleDate->format('d/m/Y H:i') . ' (d/m/Y H:i)',
            'm/d/Y h:i A' => $sampleDate->format('m/d/Y h:i A') . ' (m/d/Y h:i A)',
            'Y-m-d H:i' => $sampleDate->format('Y-m-d H:i') . ' (Y-m-d H:i)',
            'd-m-Y H:i' => $sampleDate->format('d-m-Y H:i') . ' (d-m-Y H:i)',
            'd.m.Y H:i' => $sampleDate->format('d.m.Y H:i') . ' (d.m.Y H:i)',
            'j M Y, g:i A' => $sampleDate->format('j M Y, g:i A') . ' (j M Y, g:i A)',
            'l, F j, Y g:i A' => $sampleDate->format('l, F j, Y g:i A') . ' (l, F j, Y g:i A)',
            'M d, Y H:i:s' => $sampleDate->format('M d, Y H:i:s') . ' (M d, Y H:i:s)',
        ];
    }
}
