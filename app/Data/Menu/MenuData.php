<?php

declare(strict_types=1);

namespace App\Data\Menu;

final readonly class MenuData
{
    public function __construct(
        public string $name,
        public string $label,
        public string $type,
        public ?string $url = null,
        public ?string $routeName = null,
        public string $target = '_self',
        public ?string $icon = null,
        public int $sortOrder = 0,
        public bool $isActive = true,
        public ?int $parentId = null,
    ) {}
}
