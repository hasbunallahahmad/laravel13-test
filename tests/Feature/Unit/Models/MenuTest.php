<?php

declare(strict_types=1);

use App\Models\Menu;

test('menu model exists', function () {
    expect(class_exists(Menu::class))->toBeTrue();
});

test('menu uses uuid for route model binding', function () {
    $menu = new Menu();

    expect($menu->getRouteKeyName())
        ->toBe('uuid');
});

test('menu supports soft deletes', function () {
    $traits = class_uses_recursive(Menu::class);

    expect($traits)
        ->toContain(\Illuminate\Database\Eloquent\SoftDeletes::class);
});

test('menu automatically receives a uuid', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    expect($menu->uuid)
        ->not->toBeNull()
        ->toBeString()
        ->toMatch('/^[0-9a-f-]{36}$/');
});

test('menu casts sortable and status attributes correctly', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => '10',
        'is_active' => 1,
    ]);

    expect($menu->sort_order)
        ->toBeInt()
        ->toBe(10);

    expect($menu->is_active)
        ->toBeBool()
        ->toBeTrue();
});

test('menu has parent and children relationships', function () {
    $parent = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $child = Menu::query()->create([
        'parent_id' => $parent->id,
        'name' => 'profile',
        'label' => 'Profil',
        'type' => 'url',
        'url' => '/profil',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    expect($child->parent)
        ->toBeInstanceOf(Menu::class)
        ->and($child->parent->is($parent))
        ->toBeTrue();

    expect($parent->children)
        ->toHaveCount(1)
        ->and($parent->children->first()->is($child))
        ->toBeTrue();
});

test('menu children are ordered by sort order', function () {
    $parent = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $second = Menu::query()->create([
        'parent_id' => $parent->id,
        'name' => 'second',
        'label' => 'Menu Kedua',
        'type' => 'url',
        'url' => '/kedua',
        'target' => '_self',
        'sort_order' => 20,
        'is_active' => true,
    ]);

    $first = Menu::query()->create([
        'parent_id' => $parent->id,
        'name' => 'first',
        'label' => 'Menu Pertama',
        'type' => 'url',
        'url' => '/pertama',
        'target' => '_self',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    expect($parent->children->pluck('id')->all())
        ->toBe([
            $first->id,
            $second->id,
        ]);
});
