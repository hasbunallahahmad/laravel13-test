<?php

declare(strict_types=1);

use App\Http\Requests\Admin\Menu\StoreMenuRequest;
use Illuminate\Support\Facades\Validator;

function validateMenu(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new StoreMenuRequest();

    return Validator::make(
        $data,
        $request->rules(),
    );
}

test('menu request accepts a valid url menu', function () {
    $validator = validateMenu([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'route_name' => null,
        'target' => '_self',
        'icon' => null,
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('menu request accepts a valid route menu', function () {
    $validator = validateMenu([
        'name' => 'profile',
        'label' => 'Profil',
        'type' => 'route',
        'url' => null,
        'route_name' => 'home',
        'target' => '_self',
        'icon' => null,
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('menu request requires url for url type', function () {
    $validator = validateMenu([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => null,
        'route_name' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu request requires route name for route type', function () {
    $validator = validateMenu([
        'name' => 'profile',
        'label' => 'Profil',
        'type' => 'route',
        'url' => null,
        'route_name' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu request rejects invalid target', function () {
    $validator = validateMenu([
        'name' => 'external',
        'label' => 'External',
        'type' => 'url',
        'url' => 'https://example.com',
        'route_name' => null,
        'target' => '_parent',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu request rejects non existent parent', function () {
    $validator = validateMenu([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'type' => 'url',
        'url' => '/child',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => 999999,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu request accepts an existing parent', function () {
    $parent = \App\Models\Menu::query()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
        'type' => 'url',
        'url' => '/parent',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenu([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'type' => 'url',
        'url' => '/child',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => $parent->id,
    ]);

    expect($validator->passes())->toBeTrue();
});
