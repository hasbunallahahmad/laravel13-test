<?php

namespace App\Casts;

use App\Support\Settings\SettingValueCaster;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SettingValueCast implements CastsAttributes
{
    /**
     * Cast database value to PHP value.
     */
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): mixed {
        $type = $attributes['type'] ?? 'string';

        return SettingValueCaster::get(
            $value,
            $type,
        );
    }

    /**
     * Cast PHP value to database value.
     */
    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): mixed {
        $type = $attributes['type']
            ?? $model->getAttribute('type')
            ?? 'string';

        return SettingValueCaster::set(
            $value,
            $type,
        );
    }
}
