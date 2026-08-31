<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Data\Settings\SettingData;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SettingService
{
    /**
     * Get a setting value.
     */
    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        [$group, $settingKey] = $this->parseKey($key);

        $setting = Setting::query()
            ->where('group', $group)
            ->where('key', $settingKey)
            ->first();

        return $setting?->value ?? $default;
    }

    /**
     * Create or update a setting.
     */
    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        bool $isPublic = false,
    ): Setting {
        [$group, $settingKey] = $this->parseKey($key);

        $setting = Setting::query()->firstOrNew([
            'group' => $group,
            'key' => $settingKey,
        ]);

        // Type harus ditentukan sebelum value.
        $setting->type = $type;

        $setting->value = $value;

        $setting->is_public = $isPublic;

        $setting->save();

        return $setting;
    }

    /**
     * Get all settings from a group.
     */
    public function group(string $group): Collection
    {
        return Setting::query()
            ->where('group', $group)
            ->get();
    }

    /**
     * Get all public settings.
     */
    public function public(): Collection
    {
        return Setting::query()
            ->where('is_public', true)
            ->get();
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        [$group, $settingKey] = $this->parseKey($key);

        return Setting::query()
            ->where('group', $group)
            ->where('key', $settingKey)
            ->delete() > 0;
    }

    /**
     * Forget a setting.
     *
     * Alias for delete().
     */
    public function forget(string $key): bool
    {
        return $this->delete($key);
    }

    public function all()
    {
        return Setting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get();
    }

    /**
     * Parse and validate a setting key.
     *
     * Valid format:
     *
     * group.key
     */
    private function parseKey(string $key): array
    {
        if (
            preg_match(
                '/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/',
                $key,
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'The setting key must use group.key format.',
            );
        }

        return explode('.', $key, 2);
    }

    /**
     * Store or update a setting using a SettingData object.
     */
    public function setData(SettingData $data): Setting
    {
        return $this->set(
            $data->fullKey(),
            $data->value,
            $data->type,
            $data->isPublic,
        );
    }
}
