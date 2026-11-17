<?php

declare(strict_types=1);

namespace App\Data\HomepageCta;

final readonly class HomepageCtaData
{
    /**
     * @param  list<HomepageCtaButtonData>  $buttons
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public int $sortOrder = 0,
        public bool $isActive = true,
        public array $buttons = [],
    ) {}
}
