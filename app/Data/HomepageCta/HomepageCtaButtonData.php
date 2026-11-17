<?php

declare(strict_types=1);

namespace App\Data\HomepageCta;

final readonly class HomepageCtaButtonData
{
    public function __construct(
        public string $label,
        public string $type,
        public ?string $url = null,
        public ?string $routeName = null,
        public string $target = '_self',
        public int $sortOrder = 0,
        public bool $isActive = true,
        public ?string $uuid = null,
    ) {}
}
