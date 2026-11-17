<?php

declare(strict_types=1);

namespace App\Services\HomepageCta;

use App\Services\Menu\MenuUrlValidator;

final class HomepageCtaButtonDestinationValidator
{
    /**
     * Named routes explicitly permitted as homepage CTA destinations.
     *
     * Routes requiring parameters are intentionally excluded.
     *
     * @var list<string>
     */
    private const ALLOWED_ROUTES = [
        'home',
    ];

    public function __construct(
        private readonly MenuUrlValidator $menuUrlValidator,
    ) {}

    public function isValidUrl(string $url): bool
    {
        return $this->menuUrlValidator->isValid($url);
    }

    public function isAllowedRoute(string $routeName): bool
    {
        return in_array($routeName, self::ALLOWED_ROUTES, true);
    }
}
