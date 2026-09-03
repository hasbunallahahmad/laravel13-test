<?php

declare(strict_types=1);

namespace App\Data\Media;

final readonly class MediaUpdateData
{
    public function __construct(
        public ?string $altText = null,
        public ?string $caption = null,
    ) {}
}
