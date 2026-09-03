<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Data\Settings\SettingData;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class SettingsManager
{
    private const CACHE_PREFIX = 'settings.';

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    /**
     * Get a setting value.
     */
    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        $cacheKey = $this->cacheKey($key);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $value = $this->settings->get($key);

        // Do not cache missing/null settings.
        if ($value === null) {
            return $default;
        }

        Cache::forever(
            $cacheKey,
            $value,
        );

        return $value;
    }

    /**
     * Store a setting and clear related caches.
     */
    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        bool $isPublic = false,
    ): Setting {
        $setting = $this->settings->set(
            $key,
            $value,
            $type,
            $isPublic,
        );

        $this->clearRelatedCaches(
            $key,
            $setting->group,
        );

        return $setting;
    }

    /**
     * Store or update a setting using a DTO.
     */
    public function setData(
        SettingData $data,
    ): Setting {
        return $this->set(
            $data->fullKey(),
            $data->value,
            $data->type,
            $data->isPublic,
        );
    }

    /**
     * Get all settings.
     *
     * @return Collection<int, Setting>
     */
    public function all(): Collection
    {
        return $this->settings->all();
    }

    /**
     * Delete a setting and clear related caches.
     */
    public function delete(string $key): bool
    {
        [$group] = explode('.', $key, 2);

        $deleted = $this->settings->delete($key);

        if ($deleted) {
            $this->clearRelatedCaches(
                $key,
                $group,
            );
        }

        return $deleted;
    }

    /**
     * Get settings from a group.
     *
     * @return Collection<int, Setting>
     */
    public function group(string $group): Collection
    {
        return Cache::rememberForever(
            self::CACHE_PREFIX . 'group.' . $group,
            fn(): Collection => $this->settings->group($group),
        );
    }

    /**
     * Get all public settings.
     *
     * @return Collection<int, Setting>
     */
    public function public(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_PREFIX . 'public',
            fn(): Collection => $this->settings->public(),
        );
    }

    /**
     * Forget a cached setting.
     */
    public function forget(string $key): bool
    {
        return Cache::forget(
            $this->cacheKey($key),
        );
    }

    /**
     * Clear caches affected by a setting change.
     */
    private function clearRelatedCaches(
        string $key,
        string $group,
    ): void {
        Cache::forget(
            $this->cacheKey($key),
        );

        Cache::forget(
            self::CACHE_PREFIX . 'group.' . $group,
        );

        Cache::forget(
            self::CACHE_PREFIX . 'public',
        );
    }

    /**
     * Generate cache key.
     */
    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }
}
