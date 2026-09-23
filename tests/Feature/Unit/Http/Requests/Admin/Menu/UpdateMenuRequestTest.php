<?php

declare(strict_types=1);

use App\Http\Requests\Admin\Menu\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Support\Facades\Validator;

function validateMenuUpdate(Menu $menu, array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = UpdateMenuRequest::create(
        '/admin/menus/' . $menu->uuid,
        'PATCH',
        $data,
    );

    $request->setRouteResolver(
        fn() => new class($menu)
        {
            public function __construct(
                private readonly Menu $menu,
            ) {}

            public function parameter(string $key): mixed
            {
                return $key === 'menu'
                    ? $this->menu
                    : null;
            }
        }
    );

    return Validator::make(
        $data,
        $request->rules(),
    );
}

test('update menu request class exists', function () {
    expect(class_exists(UpdateMenuRequest::class))->toBeTrue();
});

test('menu cannot use itself as parent', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => $menu->id,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu can keep its own name when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama Updated',
        'type' => 'url',
        'url' => 'https://example.com/updated',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('menu cannot use another menu name when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Menu::query()->create([
        'name' => 'about-menu',
        'label' => 'Tentang',
        'type' => 'url',
        'url' => '/about',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'about-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('menu can use another existing menu as parent when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $parent = Menu::query()->create([
        'name' => 'parent-menu',
        'label' => 'Parent',
        'type' => 'url',
        'url' => '/parent',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama Updated',
        'type' => 'url',
        'url' => 'https://example.com/',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => $parent->id,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('menu cannot use a non existent parent when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama Updated',
        'type' => 'url',
        'url' => '/',
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => 999999,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('url is required when updating menu with url type', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => null,
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('route name is required when updating menu with route type', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'url' => null,
        'route_name' => null,
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('valid url menu can be updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama Updated',
        'type' => 'url',
        'url' => 'https://example.com/updated',
        'route_name' => null,
        'target' => '_blank',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('valid route menu can be updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama Updated',
        'type' => 'route',
        'url' => null,
        'route_name' => 'admin.dashboard',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('url menu cannot contain route name when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => 'https://example.com/updated',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('route menu cannot contain url when updated', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $validator = validateMenuUpdate($menu, [
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'url' => 'https://example.com/invalid',
        'route_name' => 'admin.dashboard',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'parent_id' => null,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('update menu request converts validated data to menu data', function () {
    $menu = Menu::query()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $data = [
        'name' => 'main-menu-updated',
        'label' => 'Menu Utama Updated',
        'type' => 'url',
        'url' => 'https://example.com/updated',
        'route_name' => null,
        'target' => '_blank',
        'icon' => 'home',
        'sort_order' => 10,
        'is_active' => false,
        'parent_id' => null,
    ];

    $validator = validateMenuUpdate($menu, $data);

    expect($validator->passes())->toBeTrue();

    $request = UpdateMenuRequest::create(
        '/admin/menus/' . $menu->uuid,
        'PATCH',
        $data,
    );

    $request->setRouteResolver(
        fn() => new class($menu)
        {
            public function __construct(
                private readonly Menu $menu,
            ) {}

            public function parameter(string $key): mixed
            {
                return $key === 'menu'
                    ? $this->menu
                    : null;
            }
        }
    );

    $request->merge($data);

    $request->setValidator(
        Validator::make(
            $data,
            $request->rules(),
        ),
    );

    $menuData = $request->toData();

    expect($menuData)->toBeInstanceOf(\App\Data\Menu\MenuData::class)
        ->and($menuData->name)->toBe('main-menu-updated')
        ->and($menuData->label)->toBe('Menu Utama Updated')
        ->and($menuData->type)->toBe('url')
        ->and($menuData->url)->toBe('https://example.com/updated')
        ->and($menuData->routeName)->toBeNull()
        ->and($menuData->target)->toBe('_blank')
        ->and($menuData->icon)->toBe('home')
        ->and($menuData->sortOrder)->toBe(10)
        ->and($menuData->isActive)->toBeFalse()
        ->and($menuData->parentId)->toBeNull();
});
