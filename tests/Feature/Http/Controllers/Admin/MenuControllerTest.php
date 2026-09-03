<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\User;

test('admin menu index requires menu view permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertForbidden();
});

test('admin menu index is accessible with menu view permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();
});

test('admin menu index displays menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = \App\Models\Menu::factory()->create([
        'name' => 'profile',
        'label' => 'Profil',
    ]);

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('Profil');
});

test('admin menu index displays menus ordered by sort order', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'contact',
        'label' => 'Kontak',
        'sort_order' => 20,
    ]);

    \App\Models\Menu::factory()->create([
        'name' => 'home',
        'label' => 'Beranda',
        'sort_order' => 10,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $content = $response->getContent();

    expect(strpos($content, 'Beranda'))
        ->toBeLessThan(strpos($content, 'Kontak'));
});

test('admin menu index displays parent and child menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'sort_order' => 10,
    ]);

    \App\Models\Menu::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'library',
        'label' => 'Perpustakaan',
        'sort_order' => 10,
    ]);

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('Layanan')
        ->assertSee('Perpustakaan');
});
test('admin menu index does not display soft deleted menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'active-menu',
        'label' => 'Menu Aktif',
    ]);

    $deletedMenu = \App\Models\Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $deletedMenu->delete();

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('Menu Aktif')
        ->assertDontSee('Menu Terhapus');
});

test('admin menu index displays menu status', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'active-menu',
        'label' => 'Beranda',
        'is_active' => true,
    ]);

    \App\Models\Menu::factory()->create([
        'name' => 'inactive-menu',
        'label' => 'Profil',
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $content = $response->getContent();

    expect(substr_count($content, 'Aktif'))->toBe(1);
    expect(substr_count($content, 'Nonaktif'))->toBe(1);
});

test('admin menu index displays menu type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'home',
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
        'url' => null,
    ]);

    \App\Models\Menu::factory()->create([
        'name' => 'external',
        'label' => 'Website Eksternal',
        'type' => 'url',
        'url' => 'https://example.com',
        'route_name' => null,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee('Beranda');
    $response->assertSee('Website Eksternal');
    $response->assertSee('route');
    $response->assertSee('url');
});

test('admin menu index displays menu target', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'home',
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
    ]);

    \App\Models\Menu::factory()->create([
        'name' => 'external',
        'label' => 'Website Eksternal',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_blank',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee('_self');
    $response->assertSee('_blank');
});

test('admin menu index displays menu destination', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'home',
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
        'url' => null,
    ]);

    \App\Models\Menu::factory()->create([
        'name' => 'external',
        'label' => 'Website Eksternal',
        'type' => 'url',
        'route_name' => null,
        'url' => 'https://example.com',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee('home');
    $response->assertSee('https://example.com');
});

test('admin menu index displays menu name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'document-archive',
        'label' => 'Arsip Dokumen',
    ]);

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('document-archive');
});

test('admin menu index displays menu icon', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'library',
        'label' => 'Perpustakaan',
        'icon' => 'book-open',
    ]);

    $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('book-open');
});

test('admin menu index displays menu sort order', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    \App\Models\Menu::factory()->create([
        'name' => 'about',
        'label' => 'Tentang Kami',
        'sort_order' => 25,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee(
        'data-sort-order="25"',
        false,
    );
});

test('admin menu index displays parent menu relationship', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
    ]);

    $child = \App\Models\Menu::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'library',
        'label' => 'Perpustakaan',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee(
        'data-parent-id="' . $parent->id . '"',
        false,
    );

    $response->assertSee('Perpustakaan');
});

test('admin menu index eager loads parent relationship', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
    ]);

    \App\Models\Menu::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'library',
        'label' => 'Perpustakaan',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $menus = $response->viewData('menus');

    expect($menus)->not->toBeNull();

    foreach ($menus as $menu) {
        expect($menu->relationLoaded('parent'))->toBeTrue();
    }
});

test('admin menu create page requires menu view permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertForbidden();
});

test('admin menu create page is accessible with menu view permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();
});

test('admin menu create page provides parent menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $parentMenus = $response->viewData('parentMenus');

    expect($parentMenus)->not->toBeNull()
        ->and($parentMenus)->toHaveCount(1)
        ->and($parentMenus->first()->is($parent))->toBeTrue();
});

test('admin menu create page does not provide soft deleted parent menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $activeParent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
    ]);

    $deletedParent = \App\Models\Menu::factory()->create([
        'name' => 'archive',
        'label' => 'Arsip Lama',
    ]);

    $deletedParent->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $parentMenus = $response->viewData('parentMenus');

    expect($parentMenus)
        ->toHaveCount(1)
        ->and($parentMenus->first()->is($activeParent))->toBeTrue()
        ->and($parentMenus->contains(
            fn(\App\Models\Menu $menu): bool => $menu->is($deletedParent)
        ))->toBeFalse();
});

test('admin menu create page displays menu type options', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="type"', false);
    $response->assertSee('value="route"', false);
    $response->assertSee('value="url"', false);
});

test('admin menu create page displays name and label fields', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="name"', false);
    $response->assertSee('name="label"', false);
});

test('admin menu create page displays route name field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="route_name"', false);
});

test('admin menu create page displays url field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="url"', false);
});

test('admin menu create page displays target options', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="target"', false);
    $response->assertSee('value="_self"', false);
    $response->assertSee('value="_blank"', false);
});

test('admin menu create page displays icon field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="icon"', false);
});

test('admin menu create page displays sort order field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="sort_order"', false);
});

test('admin menu create page displays active status field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="is_active"', false);
});

test('admin menu create page displays parent menu field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="parent_id"', false);
});

test('admin menu store requires menu view permission', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'url',
            'url' => '/layanan',
        ]);

    $response->assertForbidden();
});

test('admin menu store creates a menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'url',
            'url' => '/layanan',
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);
});

test('admin menu store validates required name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'label' => 'Layanan',
            'type' => 'url',
            'url' => '/layanan',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin menu store validates required label', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'type' => 'url',
            'url' => '/layanan',
        ]);

    $response->assertSessionHasErrors('label');
});

test('admin menu store validates required type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'url' => '/layanan',
        ]);

    $response->assertSessionHasErrors('type');
});

test('admin menu store validates url for url type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'url',
        ]);

    $response->assertSessionHasErrors('url');
});

test('admin menu store validates route name for route type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'route',
        ]);

    $response->assertSessionHasErrors('route_name');
});

test('admin menu store rejects url for route type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'route',
            'route_name' => 'admin.dashboard',
            'url' => '/layanan',
        ]);

    $response->assertSessionHasErrors('url');
});

test('admin menu store rejects route name for url type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'url',
            'url' => '/layanan',
            'route_name' => 'admin.dashboard',
        ]);

    $response->assertSessionHasErrors('route_name');
});

test('admin menu update updates a menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'target' => '_blank',
            'sort_order' => 2,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'services-updated',
        'label' => 'Layanan Publik',
        'type' => 'url',
        'url' => '/layanan-publik',
        'target' => '_blank',
        'sort_order' => 2,
        'is_active' => true,
    ]);
});

test('admin menu update requires menu view permission', function () {
    $user = User::factory()->create();

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertForbidden();
});

test('admin menu update validates required name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin menu update validates required label', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('label');
});

test('admin menu update validates required type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('type');
});

test('admin menu update validates url for url type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
        ]);

    $response->assertSessionHasErrors('url');
});

test('admin menu update validates route name for route type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'route',
        ]);

    $response->assertSessionHasErrors('route_name');
});

test('admin menu update rejects url for route type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'route',
            'route_name' => 'admin.dashboard',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('url');
});

test('admin menu update rejects route name for url type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'route_name' => 'admin.dashboard',
        ]);

    $response->assertSessionHasErrors('route_name');
});

test('admin menu update validates target', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'target' => '_parent',
        ]);

    $response->assertSessionHasErrors('target');
});

test('admin menu update validates sort order', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'sort_order' => -1,
        ]);

    $response->assertSessionHasErrors('sort_order');
});

test('admin menu update validates existing parent id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'parent_id' => 999999,
        ]);

    $response->assertSessionHasErrors('parent_id');
});

test('admin menu update rejects itself as parent', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'parent_id' => $menu->id,
        ]);

    $response->assertSessionHasErrors('parent_id');
});

test('admin menu update accepts an existing parent menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
        'type' => 'url',
        'url' => '/parent',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'parent_id' => $parent->id,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'parent_id' => $parent->id,
    ]);
});

test('admin menu update rejects circular parent hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
        'type' => 'url',
        'url' => '/parent',
    ]);

    $child = Menu::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'type' => 'url',
        'url' => '/child',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $parent), [
            'name' => 'parent-menu',
            'label' => 'Parent Menu',
            'type' => 'url',
            'url' => '/parent',
            'parent_id' => $child->id,
        ]);

    $response->assertSessionHasErrors('parent_id');
});


test('admin menu update rejects soft deleted parent', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $deletedParent = Menu::factory()->create([
        'name' => 'deleted-parent',
        'label' => 'Deleted Parent',
        'type' => 'url',
        'url' => '/deleted-parent',
    ]);

    $deletedParent->delete();

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'parent_id' => $deletedParent->id,
        ]);

    $response->assertSessionHasErrors('parent_id');
});

test('admin menu update validates is active', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'is_active' => 'invalid',
        ]);

    $response->assertSessionHasErrors('is_active');
});

test('admin menu update validates icon length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services-updated',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
            'icon' => str_repeat('a', 101),
        ]);

    $response->assertSessionHasErrors('icon');
});

test('admin menu update rejects duplicate name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    Menu::factory()->create([
        'name' => 'existing-menu',
        'label' => 'Existing Menu',
        'type' => 'url',
        'url' => '/existing',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'existing-menu',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('name');
});

test('admin menu update allows keeping its own name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services',
            'label' => 'Layanan Publik',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'services',
        'label' => 'Layanan Publik',
        'url' => '/layanan-publik',
    ]);
});

test('admin menu delete soft deletes a menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertSoftDeleted('menus', [
        'id' => $menu->id,
    ]);
});
test('admin menu delete requires menu view permission', function () {
    $user = User::factory()->create();

    $menu = Menu::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response->assertForbidden();

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'deleted_at' => null,
    ]);
});

test('admin menu delete uses uuid route model binding', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $response = $this->actingAs($user)
        ->delete("/admin/menus/{$menu->uuid}");

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertSoftDeleted('menus', [
        'id' => $menu->id,
        'uuid' => $menu->uuid,
    ]);
});

test('admin menu delete nullifies children parent id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
    ]);

    $child = Menu::factory()->create([
        'name' => 'child-menu',
        'parent_id' => $parent->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $parent));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertSoftDeleted('menus', [
        'id' => $parent->id,
    ]);

    $this->assertDatabaseHas('menus', [
        'id' => $child->id,
        'parent_id' => null,
        'deleted_at' => null,
    ]);
});

test('admin menu delete cannot delete an already deleted menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $menu->delete();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response->assertNotFound();

    $this->assertSoftDeleted('menus', [
        'id' => $menu->id,
    ]);
});

test('admin menu delete preserves multiple children', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
    ]);

    $children = Menu::factory()->count(3)->create([
        'parent_id' => $parent->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $parent));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertSoftDeleted('menus', [
        'id' => $parent->id,
    ]);

    foreach ($children as $child) {
        $this->assertDatabaseHas('menus', [
            'id' => $child->id,
            'parent_id' => null,
            'deleted_at' => null,
        ]);
    }
});

test('admin menu delete preserves deep hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
    ]);

    $child = Menu::factory()->create([
        'name' => 'child-menu',
        'parent_id' => $parent->id,
    ]);

    $grandchild = Menu::factory()->create([
        'name' => 'grandchild-menu',
        'parent_id' => $child->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $parent));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertSoftDeleted('menus', [
        'id' => $parent->id,
    ]);

    $this->assertDatabaseHas('menus', [
        'id' => $child->id,
        'parent_id' => null,
        'deleted_at' => null,
    ]);

    $this->assertDatabaseHas('menus', [
        'id' => $grandchild->id,
        'parent_id' => $child->id,
        'deleted_at' => null,
    ]);
});

test('admin menu restore restores a deleted menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $menu->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'deleted_at' => null,
    ]);
});

test('admin menu restore cannot restore an active menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertNotFound();

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'deleted_at' => null,
    ]);
});

test('admin menu restore requires menu view permission', function () {
    $user = User::factory()->create();

    $menu = Menu::factory()->create();

    $menu->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertForbidden();

    $this->assertSoftDeleted('menus', [
        'id' => $menu->id,
    ]);
});

test('admin menu restore preserves children hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
    ]);

    $child = Menu::factory()->create([
        'name' => 'child-menu',
        'parent_id' => $parent->id,
    ]);

    $parent->delete();

    // DELETE service akan membuat child menjadi root.
    // Test ini memastikan restore parent tidak menghapus child
    // dan tidak membuat relasi yang tidak valid.
    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $parent));

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $parent->id,
        'deleted_at' => null,
    ]);

    $this->assertDatabaseHas('menus', [
        'id' => $child->id,
        'deleted_at' => null,
        'parent_id' => $parent->id,
    ]);
});

test('admin menu restore uses uuid route model binding', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create();

    $menu->delete();

    $response = $this->actingAs($user)
        ->patch("/admin/menus/{$menu->uuid}/restore");

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'uuid' => $menu->uuid,
        'deleted_at' => null,
    ]);
});
