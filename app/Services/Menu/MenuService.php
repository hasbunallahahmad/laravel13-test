<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Data\Menu\MenuData;
use App\Models\Menu;
use DomainException;

final class MenuService
{
    public function update(Menu $menu, MenuData $data): Menu
    {
        $this->validateParentId($data->parentId);
        $this->validateParentRelationship($menu, $data->parentId);

        $menu->update([
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
        ]);

        return $menu->refresh();
    }

    private function validateParentRelationship(Menu $menu, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        $visited = [];

        while ($parentId !== null) {
            if ($menu->id === $parentId) {
                throw new DomainException(
                    'A menu cannot be assigned to one of its descendants.'
                );
            }

            if (isset($visited[$parentId])) {
                throw new DomainException(
                    'Circular menu hierarchy detected.'
                );
            }

            $visited[$parentId] = true;

            $parent = Menu::query()->find($parentId);

            if ($parent === null) {
                throw new DomainException(
                    'The selected parent menu does not exist or has been deleted.'
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

        if (! Menu::query()->whereKey($parentId)->exists()) {
            throw new DomainException(
                'The selected parent menu does not exist or has been deleted.'
            );
        }
    }

    public function create(MenuData $data): Menu
    {
        $this->validateParentId($data->parentId);

        return Menu::query()->create([
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
        ]);
    }

    public function delete(Menu $menu): void
    {
        $menu->children()->update([
            'parent_id' => null,
        ]);

        $menu->delete();
    }
}
