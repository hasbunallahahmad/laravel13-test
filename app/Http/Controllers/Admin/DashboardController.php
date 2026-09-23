<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Media;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'users' => User::query()->count(),
            'contents' => Content::query()->count(),
            'media' => Media::query()->count(),
            'menus' => Menu::query()->count(),
        ];

        return view('admin.dashboard.index', [
            'statistics' => $statistics,
        ]);
    }
}
