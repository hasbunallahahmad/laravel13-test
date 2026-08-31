<?php

namespace App\Facades;

use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static \App\Models\Setting set(string $key, mixed $value, string $type = 'string', bool $isPublic = false)
 * @method static bool delete(string $key)
 * @method static \Illuminate\Support\Collection group(string $group)
 * @method static \Illuminate\Support\Collection public()
 * @method static void forget(string $key)
 *
 * @see \App\Services\Settings\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
