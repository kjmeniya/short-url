<?php

namespace App\Traits;

use App\Services\SettingsService;

trait DynamicPaginationTrait
{
    /**
     * Get the number of items to display per page from settings.
     *
     * @param string $context 'admin' or 'frontend'
     * @param int $default Default value if setting not found
     * @return int
     */
    protected function getPerPage(string $context = 'admin', int $default = 25): int
    {
        $settingsService = app(SettingsService::class);
        
        $settingKey = $context === 'admin' ? 'admin_items_per_page' : 'front_items_per_page';
        
        return (int) $settingsService->get($settingKey, (string) $default);
    }
}

