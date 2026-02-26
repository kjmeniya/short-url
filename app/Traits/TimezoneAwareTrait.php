<?php

namespace App\Traits;

use App\Services\TimezoneService;

trait TimezoneAwareTrait
{
    /**
     * Get the timezone service instance
     */
    protected function getTimezoneService(): TimezoneService
    {
        return app(TimezoneService::class);
    }

    /**
     * Get the user's timezone or fallback to system timezone
     */
    protected function getUserTimezone(): string
    {
        return $this->getTimezoneService()->getUserTimezone();
    }

    /**
     * Convert a date to user's timezone and format it
     */
    protected function formatDateForUser($date, ?string $format = null): string
    {
        return $this->getTimezoneService()->formatDateForUser($date, $format);
    }

    /**
     * Convert a datetime to user's timezone and format it
     */
    protected function formatDateTimeForUser($datetime, ?string $format = null): string
    {
        return $this->getTimezoneService()->formatDateTimeForUser($datetime, $format);
    }

    /**
     * Get relative time (e.g., "2 hours ago") in user's timezone
     */
    protected function formatRelativeTimeForUser($datetime): string
    {
        return $this->getTimezoneService()->formatRelativeTimeForUser($datetime);
    }

    /**
     * Format date for DataTables with timezone conversion
     */
    protected function formatDataTableDate($date, ?string $format = null): string
    {
        return $this->getTimezoneService()->formatDateForUser($date, $format);
    }

    /**
     * Format datetime for DataTables with timezone conversion
     */
    protected function formatDataTableDateTime($datetime, ?string $format = null): string
    {
        return $this->getTimezoneService()->formatDateTimeForUser($datetime, $format);
    }

    /**
     * Get timezone display name for user
     */
    protected function getUserTimezoneDisplay(): string
    {
        return $this->getTimezoneService()->getUserTimezoneDisplay();
    }

    /**
     * Convert date from user timezone to UTC for database storage
     */
    protected function convertToUtc($date, string $format = 'Y-m-d H:i:s'): string
    {
        return $this->getTimezoneService()->convertToUtc($date, $format);
    }

    /**
     * Get common date formats for admin interface
     */
    protected function getDateFormats(): array
    {
        return $this->getTimezoneService()->getCommonDateFormats();
    }

    /**
     * Format date with specific format key
     */
    protected function formatWithKey($date, string $formatKey): string
    {
        return $this->getTimezoneService()->formatWithKey($date, $formatKey);
    }
}
