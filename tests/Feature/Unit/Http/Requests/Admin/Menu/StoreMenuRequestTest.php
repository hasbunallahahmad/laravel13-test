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
        'url' => 'https://example.com/',
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
        'url' => 'https://example.com/child',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => $parent->id,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('menu request converts validated data to menu data', function () {
    $request = StoreMenuRequest::create(
        '/admin/menus',
        'POST',
        [
            'name' => 'main-menu',
            'label' => 'Menu Utama',
            'type' => 'url',
            'url' => 'https://example.com',
            'route_name' => null,
            'target' => '_blank',
            'icon' => 'home',
            'sort_order' => 10,
            'is_active' => false,
            'parent_id' => null,
        ],
    );

    $request->setContainer(app());

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    $request->setValidator($validator);

    expect($validator->passes())->toBeTrue();

    $data = $request->toData();

    expect($data)
        ->toBeInstanceOf(\App\Data\Menu\MenuData::class)
        ->and($data->name)->toBe('main-menu')
        ->and($data->label)->toBe('Menu Utama')
        ->and($data->type)->toBe('url')
        ->and($data->url)->toBe('https://example.com')
        ->and($data->routeName)->toBeNull()
        ->and($data->target)->toBe('_blank')
        ->and($data->icon)->toBe('home')
        ->and($data->sortOrder)->toBe(10)
        ->and($data->isActive)->toBeFalse()
        ->and($data->parentId)->toBeNull();
});
