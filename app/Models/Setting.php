<?php

namespace App\Models;

use App\Casts\SettingValueCast;
use App\Support\Settings\SettingValueCaster;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'group',
    'key',
    'value',
    'type',
    'is_public',
])]
#[Hidden([
    'id',
])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * Generate UUID automatically when creating a setting.
     */
    protected static function booted(): void
    {
        static::creating(function (Setting $setting): void {
            if (empty($setting->uuid)) {
                $setting->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Cast attributes to their appropriate types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => SettingValueCast::class,
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the setting value converted according to its declared type.
     */
    public function getTypedValueAttribute(): mixed
    {
        return SettingValueCaster::get(
            $this->getRawOriginal('value'),
            $this->type,
        );
    }

    /**
     * Use UUID for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
