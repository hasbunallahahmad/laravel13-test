<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;

final class PublicMenuService
{
    public function __construct(
        private readonly MenuUrlValidator $menuUrlValidator,
    ) {}

    /**
     * Get active public menus and prepare safe destination URLs.
     *
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();

        return $menus->map(function (Menu $menu): Menu {
            $menu->setAttribute(
                'public_url',
                $this->resolveMenuUrl($menu),
            );

            $menu->children->each(function (Menu $child): void {
                $child->setAttribute(
                    'public_url',
                    $this->resolveMenuUrl($child),
                );
            });

            return $menu;
        });
    }

    private function resolveMenuUrl(Menu $menu): ?string
    {
        if ($menu->type === 'route') {
            // Saat ini, hanya route home yang diizinkan.
            if (
                $menu->route_name !== 'home'
                || ! Route::has('home')
            ) {
                return null;
            }

            return route('home');
        }

        if (
            $menu->type !== 'url'
            || ! is_string($menu->url)
        ) {
            return null;
        }

        $url = trim($menu->url);

        return $this->menuUrlValidator->isValid($url)
            ? $url
            : null;
    }
}
