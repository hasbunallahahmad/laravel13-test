<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\StoreMenuRequest;
use App\Http\Requests\Admin\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\Menu\MenuService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

final class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menus = $this->buildMenuTree($menus);

        return view('admin.menus.index', [
            'menus' => $menus,
        ]);
    }

    public function create(): View
    {
        $parentMenus = Menu::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.menus.create', [
            'parentMenus' => $parentMenus,
        ]);
    }

    public function edit(
        Menu $menu,
        MenuService $menuService,
    ): View {
        $descendantIds = $menuService->getDescendantIds($menu);

        $excludedIds = [
            $menu->id,
            ...$descendantIds,
        ];

        $parentMenus = Menu::query()
            ->whereNotIn('id', $excludedIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'parentMenus' => $parentMenus,
            'descendantIds' => $descendantIds,
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

    public function forceDestroy(
        Menu $menu,
        MenuService $menuService,
    ): RedirectResponse {
        if (! $menu->trashed()) {
            abort(404);
        }

        $menuService->forceDelete($menu);

        return redirect()
            ->route('admin.menus.trash');
    }

    public function trash(): View
    {
        $menus = Menu::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.menus.trash', [
            'menus' => $menus,
        ]);
    }

    /**
     * Build the complete menu hierarchy in memory.
     *
     * @param Collection<int, Menu> $menus
     * @return Collection<int, Menu>
     */
    private function buildMenuTree(Collection $menus): Collection
    {
        $menusByParent = $menus->groupBy(
            static fn(Menu $menu): string => (string) $menu->parent_id,
        );

        $build = function (Collection $items) use (&$build, $menusByParent): void {
            $items->each(function (Menu $menu) use (&$build, $menusByParent): void {
                $children = $menusByParent
                    ->get((string) $menu->id, new Collection())
                    ->values();

                $menu->setRelation('children', $children);

                $build($children);
            });
        };

        $roots = $menusByParent
            ->get('', new Collection())
            ->values();

        $build($roots);

        return $roots;
    }
}
