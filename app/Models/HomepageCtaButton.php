<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HomepageCtaButton extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'homepage_cta_id',
        'label',
        'type',
        'url',
        'route_name',
        'target',
        'sort_order',
        'is_active',
        'deleted_with_homepage_cta',
    ];

    protected function casts(): array
    {
        return [
            'homepage_cta_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'deleted_with_homepage_cta' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $homepageCtaButton): void {
            if (blank($homepageCtaButton->uuid)) {
                $homepageCtaButton->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function homepageCta(): BelongsTo
    {
        return $this->belongsTo(HomepageCta::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
