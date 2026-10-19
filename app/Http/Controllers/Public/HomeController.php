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

        // Tolak URL kosong, karakter kontrol, dan backslash.
        if (
            $url === ''
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $url)
        ) {
            return null;
        }

        // Validasi path lokal, termasuk URL encoding berlapis.
        if (str_starts_with($url, '/')) {
            $decodedPath = $url;

            // Batasi decoding untuk menghindari encoding berlapis tanpa batas.
            for ($i = 0; $i < 5; $i++) {
                if (
                    str_starts_with($decodedPath, '//')
                    || preg_match(
                        '/[\x00-\x1F\x7F\\\\]/',
                        $decodedPath,
                    )
                ) {
                    return null;
                }

                $nextDecodedPath = rawurldecode($decodedPath);

                if ($nextDecodedPath === $decodedPath) {
                    break;
                }

                $decodedPath = $nextDecodedPath;
            }

            // Periksa kembali hasil decoding terakhir.
            if (
                str_starts_with($decodedPath, '//')
                || preg_match(
                    '/[\x00-\x1F\x7F\\\\]/',
                    $decodedPath,
                )
            ) {
                return null;
            }

            // Pertahankan URL aslinya jika path lokal lolos validasi.
            return $url;
        }

        // Untuk URL absolut, hanya HTTP dan HTTPS yang diperbolehkan.
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower(
            (string) parse_url($url, PHP_URL_SCHEME),
        );

        $host = parse_url($url, PHP_URL_HOST);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || ! is_string($host)
            || $host === ''
            || $user !== null
            || $pass !== null
        ) {
            return null;
        }

        return $url;
    }
}
