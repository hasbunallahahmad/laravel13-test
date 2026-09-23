<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentBlockType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LogicException;

class ContentBlock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'content_id',
        'type',
        'data',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentBlockType::class,
            'data' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ContentBlock $block): void {
            if (empty($block->uuid)) {
                $block->uuid = (string) Str::uuid();
            }
        });

        static::updating(function (ContentBlock $block): void {
            if ($block->isDirty('uuid')) {
                throw new LogicException('Content block UUID cannot be changed.');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
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
        return $query->orderBy('sort_order');
    }

    public function scopeOfType(
        Builder $query,
        ContentBlockType $type,
    ): Builder {
        return $query->where('type', $type);
    }
}
