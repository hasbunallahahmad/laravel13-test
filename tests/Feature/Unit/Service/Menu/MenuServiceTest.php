<?php

declare(strict_types=1);

use App\Data\Menu\MenuData;
use App\Models\Menu;
use App\Services\Menu\MenuService;

test('menu service class exists', function () {
    expect(class_exists(MenuService::class))->toBeTrue();
});

test('menu service creates a menu from menu data', function () {
    $service = app(MenuService::class);

    $menuData = new MenuData(
        name: 'main-menu',
        label: 'Menu Utama',
        type: 'url',
        url: '/',
        target: '_self',
        sortOrder: 0,
        isActive: true,
    );

    $menu = $service->create($menuData);

    expect($menu)
        ->toBeInstanceOf(Menu::class)
        ->and($menu->name)->toBe('main-menu')
        ->and($menu->label)->toBe('Menu Utama')
        ->and($menu->type)->toBe('url')
        ->and($menu->url)->toBe('/')
        ->and($menu->route_name)->toBeNull()
        ->and($menu->target)->toBe('_self')
        ->and($menu->sort_order)->toBe(0)
        ->and($menu->is_active)->toBeTrue();

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
    ]);
});

test('menu service updates a menu from menu data', function () {
    $service = app(MenuService::class);

    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $menuData = new MenuData(
        name: 'main-menu-updated',
        label: 'Menu Utama Updated',
        type: 'route',
        routeName: 'admin.dashboard',
        target: '_blank',
        icon: 'dashboard',
        sortOrder: 10,
        isActive: false,
        parentId: null,
    );

    $updatedMenu = $service->update($menu, $menuData);

    expect($updatedMenu)
        ->toBeInstanceOf(Menu::class)
        ->and($updatedMenu->id)->toBe($menu->id)
        ->and($updatedMenu->name)->toBe('main-menu-updated')
        ->and($updatedMenu->label)->toBe('Menu Utama Updated')
        ->and($updatedMenu->type)->toBe('route')
        ->and($updatedMenu->url)->toBeNull()
        ->and($updatedMenu->route_name)->toBe('admin.dashboard')
        ->and($updatedMenu->target)->toBe('_blank')
        ->and($updatedMenu->icon)->toBe('dashboard')
        ->and($updatedMenu->sort_order)->toBe(10)
        ->and($updatedMenu->is_active)->toBeFalse()
        ->and($updatedMenu->parent_id)->toBeNull();

    expect(Menu::query()->find($menu->id)->name)
        ->toBe('main-menu-updated');
});

test('menu service rejects circular parent relationship', function () {
    $service = app(MenuService::class);

    $menuA = Menu::query()->create([
        'name' => 'menu-a',
        'label' => 'Menu A',
        'type' => 'url',
        'url' => '/a',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $menuB = Menu::query()->create([
        'name' => 'menu-b',
        'label' => 'Menu B',
        'type' => 'url',
        'url' => '/b',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => $menuA->id,
    ]);

    $menuData = new MenuData(
        name: 'menu-a',
        label: 'Menu A Updated',
        type: 'url',
        url: '/a-updated',
        target: '_self',
        sortOrder: 0,
        isActive: true,
        parentId: $menuB->id,
    );

    expect(fn() => $service->update($menuA, $menuData))
        ->toThrow(\DomainException::class);
});

test('menu service rejects deep circular parent relationship', function () {
    $service = app(MenuService::class);

    $menuA = Menu::query()->create([
        'name' => 'menu-a',
        'label' => 'Menu A',
        'type' => 'url',
        'url' => '/a',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $menuB = Menu::query()->create([
        'name' => 'menu-b',
        'label' => 'Menu B',
        'type' => 'url',
        'url' => '/b',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => $menuA->id,
    ]);

    $menuC = Menu::query()->create([
        'name' => 'menu-c',
        'label' => 'Menu C',
        'type' => 'url',
        'url' => '/c',
        'target' => '_self',
        'sort_order' => 2,
        'is_active' => true,
        'parent_id' => $menuB->id,
    ]);

    $menuD = Menu::query()->create([
        'name' => 'menu-d',
        'label' => 'Menu D',
        'type' => 'url',
        'url' => '/d',
        'target' => '_self',
        'sort_order' => 3,
        'is_active' => true,
        'parent_id' => $menuC->id,
    ]);

    $menuData = new MenuData(
        name: 'menu-a',
        label: 'Menu A Updated',
        type: 'url',
        url: '/a-updated',
        target: '_self',
        sortOrder: 0,
        isActive: true,
        parentId: $menuD->id,
    );

    expect(fn() => $service->update($menuA, $menuData))
        ->toThrow(\DomainException::class);
});

test('menu service rejects soft deleted parent relationship', function () {
    $service = app(MenuService::class);

    $parent = Menu::query()->create([
        'name' => 'deleted-parent',
        'label' => 'Deleted Parent',
        'type' => 'url',
        'url' => '/deleted-parent',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $menu = Menu::query()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'type' => 'url',
        'url' => '/child',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $parent->delete();

    $menuData = new MenuData(
        name: 'child-menu',
        label: 'Child Menu Updated',
        type: 'url',
        url: '/child-updated',
        target: '_self',
        sortOrder: 1,
        isActive: true,
        parentId: $parent->id,
    );

    expect(fn() => $service->update($menu, $menuData))
        ->toThrow(DomainException::class);
});

test('menu service rejects soft deleted parent when creating menu', function () {
    $service = app(MenuService::class);

    $parent = Menu::query()->create([
        'name' => 'deleted-parent',
        'label' => 'Deleted Parent',
        'type' => 'url',
        'url' => '/deleted-parent',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $parent->delete();

    $menuData = new MenuData(
        name: 'child-menu',
        label: 'Child Menu',
        type: 'url',
        url: '/child',
        target: '_self',
        sortOrder: 1,
        isActive: true,
        parentId: $parent->id,
    );

    expect(fn() => $service->create($menuData))
        ->toThrow(DomainException::class);
});
