<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class HomepageSection extends Model
{
    protected $fillable = [
        'is_enabled',
        'sort_order',
        'config_version',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'config_version' => 'integer',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $section): void {
            if ($section->isDirty('key')) {
                throw new LogicException(
                    'Homepage section key cannot be changed after creation.'
                );
            }
        });
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDisabled(Builder $query): Builder
    {
        return $query->where('is_enabled', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
