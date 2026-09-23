<?php

declare(strict_types=1);

namespace App\Models;

use LogicException;
use App\Models\User;
use App\Enums\ContentType;
use App\Enums\ContentStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
        'author_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Content $content): void {
            if (empty($content->uuid)) {
                $content->uuid = (string) Str::uuid();
            }

            if (! empty($content->slug)) {
                $content->slug = Str::slug($content->slug);
            }
        });

        static::updating(function (Content $content): void {
            if ($content->isDirty('uuid')) {
                throw new LogicException('Content UUID cannot be changed.');
            }

            if ($content->isDirty('slug') && ! empty($content->slug)) {
                $content->slug = Str::slug($content->slug);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)
            ->orderBy('sort_order');
    }

    public function isPage(): bool
    {
        return $this->type === ContentType::PAGE;
    }

    // public function resolveSoftDeletableRouteBinding(
    //     mixed $value,
    //     ?string $field = null,
    // ): ?Model {
    //     return $this->resolveRouteBindingQuery(
    //         $this,
    //         $value,
    //         $field,
    //     )
    //         ->withTrashed()
    //         ->first();
    // }

    /**
     * SCOPE Slug.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopeReview(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::REVIEW);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::ARCHIVED);
    }

    public function scopeOfType(Builder $query, ContentType $type): Builder
    {
        return $query->where('type', $type);
    }
}
