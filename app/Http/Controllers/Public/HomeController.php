<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

final class HomeController extends Controller
{
    public function __construct(
        private readonly MenuUrlValidator $menuUrlValidator,
    ) {}

    public function __invoke(): View
    {
        $news = Content::query()
            ->published()
            ->where('type', ContentType::NEWS)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

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

        $menus = $menus->map(function (Menu $menu): Menu {
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

        $settings = Setting::query()
            ->where('is_public', true)
            ->get()
            ->keyBy(
                fn(Setting $setting): string => $setting->group
                    . '.'
                    . $setting->key,
            );

        return view('public.home', [
            'news' => $news,
            'menus' => $menus,
            'settings' => $settings,
        ]);
    }

    private function resolveMenuUrl(Menu $menu): ?string
    {
        if ($menu->type === 'route') {
            // Untuk saat ini, hanya izinkan route publik tanpa parameter wajib.
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
