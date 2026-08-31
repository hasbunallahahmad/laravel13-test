<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsManager
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

        // Jangan cache default/null untuk setting yang tidak ada.
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
     * Store a setting and clear its cache.
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

        $this->forget($key);

        return $setting;
    }

    /**
     * Delete a setting and clear its cache.
     */
    public function delete(string $key): bool
    {
        $deleted = $this->settings->delete($key);

        if ($deleted) {
            $this->forget($key);
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
            fn(): Collection => Setting::query()
                ->where('is_public', true)
                ->get(),
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
     * Generate cache key.
     */
    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }
}
