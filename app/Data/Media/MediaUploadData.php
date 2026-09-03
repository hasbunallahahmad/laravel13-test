<?php

declare(strict_types=1);

namespace App\Data\Media;

use Illuminate\Http\UploadedFile;

final readonly class MediaUploadData
{
    public function __construct(
        public UploadedFile $file,
        public ?string $altText = null,
        public ?string $caption = null,
        public ?int $uploadedBy = null,
    ) {}
}
