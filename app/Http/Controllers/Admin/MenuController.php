<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\StoreMenuRequest;
use App\Http\Requests\Admin\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\Menu\MenuService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->whereNull('parent_id')
            ->with('parent', 'children')
            ->orderBy('sort_order')
            ->get();

        return view('admin.menus.index', [
            'menus' => $menus,
        ]);
    }

    public function create(): View
    {
        $parentMenus = Menu::query()
            ->orderBy('sort_order')
            ->get();

        return view('admin.menus.create', [
            'parentMenus' => $parentMenus,
        ]);
    }
    public function edit(Menu $menu): View
    {
        $parentMenus = Menu::query()
            ->whereNull('deleted_at')
            ->whereKeyNot($menu->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'parentMenus' => $parentMenus,
        ]);
    }

    public function store(
        StoreMenuRequest $request,
        MenuService $menuService,
    ): RedirectResponse {
        $menuService->create(
            $request->toData(),
        );

        return redirect()
            ->route('admin.menus.index');
    }

    public function update(
        UpdateMenuRequest $request,
        Menu $menu,
        MenuService $menuService,
    ): RedirectResponse {
        try {
            $menuService->update(
                $menu,
                $request->toData(),
            );
        } catch (DomainException $exception) {
            return back()
                ->withErrors([
                    'parent_id' => $exception->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.menus.index');
    }

    public function destroy(
        Menu $menu,
        MenuService $menuService,
    ): RedirectResponse {
        $menuService->delete($menu);

        return redirect()
            ->route('admin.menus.index');
    }

    public function restore(Menu $menu): RedirectResponse
    {
        if (! $menu->trashed()) {
            abort(404);
        }

        $menu->restore();

        return redirect()
            ->route('admin.menus.index');
    }

    public function trash(): View
    {
        $menus = Menu::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('admin.menus.trash', [
            'menus' => $menus,
        ]);
    }
}
