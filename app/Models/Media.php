<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'original_name',
    'file_name',
    'disk',
    'path',
    'mime_type',
    'extension',
    'size',
    'metadata',
    'alt_text',
    'caption',
    'uploaded_by',
])]
#[Hidden([
    'id',
])]
final class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Generate UUID automatically.
     */
    protected static function booted(): void
    {
        static::creating(function (Media $media): void {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
        ];
    }

    /**
     * User who uploaded this media.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
        );
    }

    /**
     * Use UUID for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the relative storage file path.
     */
    public function getStoragePathAttribute(): string
    {
        return trim(
            $this->path,
            '/',
        ) . '/' . $this->file_name;
    }
}
