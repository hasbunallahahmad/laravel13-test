<?php

declare(strict_types=1);

use App\Data\Menu\MenuData;

test('menu data stores menu attributes', function () {
    $data = new MenuData(
        name: 'main-menu',
        label: 'Menu Utama',
        type: 'url',
        url: '/',
        routeName: null,
        target: '_self',
        icon: null,
        sortOrder: 0,
        isActive: true,
        parentId: null,
    );

    expect($data->name)->toBe('main-menu')
        ->and($data->label)->toBe('Menu Utama')
        ->and($data->type)->toBe('url')
        ->and($data->url)->toBe('/')
        ->and($data->routeName)->toBeNull()
        ->and($data->target)->toBe('_self')
        ->and($data->icon)->toBeNull()
        ->and($data->sortOrder)->toBe(0)
        ->and($data->isActive)->toBeTrue()
        ->and($data->parentId)->toBeNull();
});

test('menu data supports update data', function () {
    $menuData = new MenuData(
        name: 'main-menu-updated',
        label: 'Menu Utama Updated',
        type: 'route',
        routeName: 'admin.dashboard',
        target: '_blank',
        icon: 'dashboard',
        sortOrder: 10,
        isActive: false,
        parentId: 5,
    );

    expect($menuData->name)->toBe('main-menu-updated')
        ->and($menuData->label)->toBe('Menu Utama Updated')
        ->and($menuData->type)->toBe('route')
        ->and($menuData->url)->toBeNull()
        ->and($menuData->routeName)->toBe('admin.dashboard')
        ->and($menuData->target)->toBe('_blank')
        ->and($menuData->icon)->toBe('dashboard')
        ->and($menuData->sortOrder)->toBe(10)
        ->and($menuData->isActive)->toBeFalse()
        ->and($menuData->parentId)->toBe(5);
});
