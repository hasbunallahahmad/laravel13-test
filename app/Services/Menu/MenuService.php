<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Data\Menu\MenuData;
use App\Models\Menu;
use DomainException;
use Illuminate\Support\Facades\DB;

final class MenuService
{
    public function create(MenuData $data): Menu
    {
        $this->validateParentId($data->parentId);

        return Menu::query()->create(
            $this->toAttributes($data),
        );
    }

    public function update(Menu $menu, MenuData $data): Menu
    {
        $this->validateParentRelationship($menu, $data->parentId);

        $menu->update(
            $this->toAttributes($data),
        );

        return $menu->refresh();
    }

    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu): void {
            $menu->children()->update([
                'parent_id' => null,
            ]);

            $menu->delete();
        });
    }

    public function forceDelete(Menu $menu): void
    {
        DB::transaction(function () use ($menu): void {
            $menu->forceDelete();
        });
    }

    /**
     * Return the IDs of all descendants of the given menu.
     *
     * @return list<int>
     */
    public function getDescendantIds(Menu $menu): array
    {
        $descendantIds = [];
        $parentIds = [$menu->id];

        while ($parentIds !== []) {
            $children = Menu::query()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id');

            if ($children->isEmpty()) {
                break;
            }

            $childrenIds = $children
                ->map(static fn(mixed $id): int => (int) $id)
                ->all();

            $descendantIds = [
                ...$descendantIds,
                ...$childrenIds,
            ];

            $parentIds = $childrenIds;
        }

        return $descendantIds;
    }

    private function validateParentRelationship(
        Menu $menu,
        ?int $parentId,
    ): void {
        if ($parentId === null) {
            return;
        }

        $visited = [];

        while ($parentId !== null) {
            if ($menu->id === $parentId) {
                throw new DomainException(
                    'A menu cannot be assigned to one of its descendants.',
                );
            }

            if (isset($visited[$parentId])) {
                throw new DomainException(
                    'Circular menu hierarchy detected.',
                );
            }

            $visited[$parentId] = true;

            $parent = Menu::query()->find($parentId);

            if ($parent === null) {
                throw new DomainException(
                    'The selected parent menu does not exist or has been deleted.',
                );
            }

            $parentId = $parent->parent_id;
        }
    }

    private function validateParentId(?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        $parentExists = Menu::query()
            ->whereKey($parentId)
            ->exists();

        if (! $parentExists) {
            throw new DomainException(
                'The selected parent menu does not exist or has been deleted.',
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toAttributes(MenuData $data): array
    {
        return [
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'label' => $data->label,
            'type' => $data->type,
            'url' => $data->url,
            'route_name' => $data->routeName,
            'target' => $data->target,
            'icon' => $data->icon,
            'sort_order' => $data->sortOrder,
            'is_active' => $data->isActive,
        ];
    }
}
