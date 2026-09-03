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
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->with('parent')
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

    public function store(
        StoreMenuRequest $request,
        MenuService $menuService,
    ): RedirectResponse {
        $menuService->create(
            new \App\Data\Menu\MenuData(
                name: $request->validated('name'),
                label: $request->validated('label'),
                type: $request->validated('type'),
                url: $request->validated('url'),
                routeName: $request->validated('route_name'),
                target: $request->validated('target') ?? '_self',
                icon: $request->validated('icon'),
                sortOrder: $request->validated('sort_order') ?? 0,
                isActive: $request->validated('is_active') ?? true,
                parentId: $request->validated('parent_id'),
            ),
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
}
