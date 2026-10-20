<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Setting;
use App\Services\Menu\PublicMenuService;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __construct(
        private readonly PublicMenuService $publicMenuService,
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

        $menus = $this->publicMenuService->getMenus();

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
}
