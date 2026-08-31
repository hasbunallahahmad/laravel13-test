<?php

use App\Services\Settings\SettingsManager;

if (! function_exists('settings')) {
    /**
     * Get the settings manager or retrieve a setting value.
     */
    function settings(
        ?string $key = null,
        mixed $default = null,
    ): mixed {
        $manager = app(SettingsManager::class);

        if ($key === null) {
            return $manager;
        }

        return $manager->get($key, $default);
    }
}
