<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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

test('admin menu create page requires menu view permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertForbidden();
});

test('admin menu create page is accessible with menu create permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();
});


test('admin menu create page is accessible with menu create permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();
});

test('admin menu create page provides parent menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $parent = Menu::factory()->create([
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

test('admin menu create page displays menu type options', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="type"', false);
    $response->assertSee('value="route"', false);
    $response->assertSee('value="url"', false);
});

test('admin menu create page displays name and label fields', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="name"', false);
    $response->assertSee('name="label"', false);
});

test('admin menu create page displays route name field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="route_name"', false);
});

test('admin menu create page displays url field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="url"', false);
});

test('admin menu create page displays target options', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="target"', false);
    $response->assertSee('value="_self"', false);
    $response->assertSee('value="_blank"', false);
});

test('admin menu create page displays icon field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="icon"', false);
});

test('admin menu create page displays sort order field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="sort_order"', false);
});

test('admin menu create page displays active status field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="is_active"', false);
});

test('admin menu create page displays parent menu field', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertOk();

    $response->assertSee('name="parent_id"', false);
});

test('admin menu store requires menu create permission', function () {
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

    $user->givePermissionTo('menus.create');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'services',
            'label' => 'Layanan',
            'type' => 'url',
            'url' => 'https://example.com/layanan',
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => 'https://example.com/layanan',
    ]);
});

test('admin menu store validates required name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.create');

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

    $user->givePermissionTo('menus.update');

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
            'url' => 'https://example.com/layanan-publik',
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
        'url' => 'https://example.com/layanan-publik',
        'target' => '_blank',
        'sort_order' => 2,
        'is_active' => true,
    ]);
});

test('admin menu update requires menu update permission', function () {
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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

    $menu = Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'services',
            'type' => 'url',
            'url' => '/layanan-publik',
        ]);

    $response->assertSessionHasErrors('label');
});

test('admin menu update validates required type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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
            'url' => 'https://example.com/layanan-publik',
            'parent_id' => $parent->id,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'parent_id' => $parent->id,
        'url' => 'https://example.com/layanan-publik',
    ]);
});

test('admin menu update rejects circular parent hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

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
            'url' => 'https://example.com/parent',
            'parent_id' => $child->id,
        ]);

    $response->assertSessionHasErrors('parent_id');
});


test('admin menu update rejects soft deleted parent', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

    $menu = Menu::factory()->create();

    $parent = Menu::factory()->create();

    $parent->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => $parent->id,
        ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => $parent->id,
        ]);

    $menu->refresh();

    expect($menu->parent_id)->toBeNull();
});

test('admin menu update validates is active', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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
            'url' => 'https://example.com/layanan-publik',
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'services',
        'label' => 'Layanan Publik',
        'url' => 'https://example.com/layanan-publik',
    ]);
});

test('admin menu delete soft deletes a menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

    $user->givePermissionTo('menus.update');

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

test('admin menu store rejects soft deleted parent', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create();

    $parent->delete();

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'child-menu',
            'label' => 'Child Menu',
            'type' => 'url',
            'url' => '/child',
            'parent_id' => $parent->id,
        ]);

    $response->assertSessionHasErrors('parent_id');

    $this->assertDatabaseMissing('menus', [
        'name' => 'child-menu',
    ]);
});

test('admin menu update cannot change uuid', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $originalUuid = $menu->uuid;

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu->refresh();

    expect($menu->uuid)->toBe($originalUuid);
});

test('admin menu update cannot change id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $originalId = $menu->id;

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'id' => 999999,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu->refresh();

    expect($menu->id)->toBe($originalId);
});
test('admin menu update rejects direct circular hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
    ]);

    $child = Menu::factory()->create([
        'name' => 'child-menu',
        'parent_id' => $parent->id,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $parent), [
            'name' => $parent->name,
            'label' => $parent->label,
            'type' => 'url',
            'url' => 'https://example.com/parent',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => $child->id,
        ]);

    $response->assertSessionHasErrors('parent_id');

    $parent->refresh();

    expect($parent->parent_id)->toBeNull();
});
test('admin menu update rejects deep circular hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.update');

    $grandParent = Menu::factory()->create([
        'name' => 'grand-parent',
    ]);

    $parent = Menu::factory()->create([
        'name' => 'parent',
        'parent_id' => $grandParent->id,
    ]);

    $child = Menu::factory()->create([
        'name' => 'child',
        'parent_id' => $parent->id,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $grandParent), [
            'name' => $grandParent->name,
            'label' => $grandParent->label,
            'type' => 'url',
            'url' => 'https://example.com/grand-parent',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => $child->id,
        ]);

    $response->assertSessionHasErrors('parent_id');

    $grandParent->refresh();

    expect($grandParent->parent_id)->toBeNull();
});

test('admin menu store rejects name used by soft deleted menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $deletedMenu = Menu::factory()->create([
        'name' => 'deleted-menu',
    ]);

    $deletedMenu->delete();

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'deleted-menu',
            'label' => 'New Menu',
            'type' => 'url',
            'url' => '/new-menu',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('menus', [
        'label' => 'New Menu',
    ]);
});
test('admin menu update rejects name used by soft deleted menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'active-menu',
    ]);

    $deletedMenu = Menu::factory()->create([
        'name' => 'deleted-menu',
    ]);

    $deletedMenu->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'deleted-menu',
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $menu->refresh();

    expect($menu->name)->toBe('active-menu');
});
test('admin menu store rejects invalid type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-type-menu',
            'label' => 'Invalid Type Menu',
            'type' => 'javascript',
            'url' => '/invalid',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('type');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-type-menu',
    ]);
});
test('admin menu update rejects invalid type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'javascript',
            'url' => '/invalid',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('type');

    $menu->refresh();

    expect($menu->type)->toBe('url');
    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects invalid target', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-target-menu',
            'label' => 'Invalid Target Menu',
            'type' => 'url',
            'url' => '/invalid',
            'target' => 'javascript',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('target');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-target-menu',
    ]);
});

test('admin menu update rejects invalid target', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
        'target' => '_self',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => 'javascript',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('target');

    $menu->refresh();

    expect($menu->target)->toBe('_self');
    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects negative sort order', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'negative-sort-menu',
            'label' => 'Negative Sort Menu',
            'type' => 'url',
            'url' => '/negative-sort',
            'target' => '_self',
            'sort_order' => -1,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('sort_order');

    $this->assertDatabaseMissing('menus', [
        'name' => 'negative-sort-menu',
    ]);
});
test('admin menu update rejects negative sort order', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
        'sort_order' => 5,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => -1,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('sort_order');

    $menu->refresh();

    expect($menu->sort_order)->toBe(5);
    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects invalid is active value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-status-menu',
            'label' => 'Invalid Status Menu',
            'type' => 'url',
            'url' => '/invalid-status',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => 'invalid',
        ]);

    $response->assertSessionHasErrors('is_active');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-status-menu',
    ]);
});
test('admin menu update rejects invalid is active value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => 'invalid',
        ]);

    $response->assertSessionHasErrors('is_active');

    $menu->refresh();

    expect($menu->is_active)->toBeTrue();
    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects icon exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'long-icon-menu',
            'label' => 'Long Icon Menu',
            'type' => 'url',
            'url' => '/long-icon',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'icon' => str_repeat('a', 101),
        ]);

    $response->assertSessionHasErrors('icon');

    $this->assertDatabaseMissing('menus', [
        'name' => 'long-icon-menu',
    ]);
});
test('admin menu update rejects icon exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
        'icon' => 'home',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'icon' => str_repeat('a', 101),
        ]);

    $response->assertSessionHasErrors('icon');

    $menu->refresh();

    expect($menu->icon)->toBe('home');
    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects name exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => str_repeat('a', 256),
            'label' => 'Long Name Menu',
            'type' => 'url',
            'url' => '/long-name',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('menus', [
        'label' => 'Long Name Menu',
    ]);
});
test('admin menu update rejects name exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'original-menu',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => str_repeat('a', 256),
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $menu->refresh();

    expect($menu->name)->toBe('original-menu');
});
test('admin menu store rejects label exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'long-label-menu',
            'label' => str_repeat('a', 256),
            'type' => 'url',
            'url' => '/long-label',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('label');

    $this->assertDatabaseMissing('menus', [
        'name' => 'long-label-menu',
    ]);
});
test('admin menu update rejects label exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'label' => 'Original Label',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => str_repeat('a', 256),
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('label');

    $menu->refresh();

    expect($menu->label)->toBe('Original Label');
});
test('admin menu store rejects url exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'long-url-menu',
            'label' => 'Long URL Menu',
            'type' => 'url',
            'url' => str_repeat('a', 2049),
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('url');

    $this->assertDatabaseMissing('menus', [
        'name' => 'long-url-menu',
    ]);
});
test('admin menu update rejects url exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => str_repeat('a', 2049),
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('url');

    $menu->refresh();

    expect($menu->url)->toBe('/original');
});
test('admin menu store rejects route name exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'long-route-name-menu',
            'label' => 'Long Route Name Menu',
            'type' => 'route',
            'route_name' => str_repeat('a', 256),
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $this->assertDatabaseMissing('menus', [
        'name' => 'long-route-name-menu',
    ]);
});
test('admin menu update rejects route name exceeding maximum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'route',
        'route_name' => 'admin.dashboard',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'route',
            'route_name' => str_repeat('a', 256),
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $menu->refresh();

    expect($menu->route_name)->toBe('admin.dashboard');
});
test('admin menu store rejects non integer parent id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-parent-type-menu',
            'label' => 'Invalid Parent Type Menu',
            'type' => 'url',
            'url' => '/invalid-parent-type',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => 'not-an-integer',
        ]);

    $response->assertSessionHasErrors('parent_id');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-parent-type-menu',
    ]);
});
test('admin menu update rejects non integer parent id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => 'not-an-integer',
        ]);

    $response->assertSessionHasErrors('parent_id');

    $menu->refresh();

    expect($menu->parent_id)->toBeNull();
    expect($menu->url)->toBe('/original');
});

test('guest cannot access admin menus', function () {
    $response = $this->get(route('admin.menus.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot store admin menu', function () {
    $response = $this->post(route('admin.menus.store'), [
        'name' => 'guest-menu',
        'label' => 'Guest Menu',
        'type' => 'url',
        'url' => '/guest',
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('menus', [
        'name' => 'guest-menu',
    ]);
});

test('guest cannot update admin menu', function () {
    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->patch(route('admin.menus.update', $menu), [
        'name' => $menu->name,
        'label' => $menu->label,
        'type' => 'url',
        'url' => '/hacked',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('login'));

    $menu->refresh();

    expect($menu->url)->toBe('/original');
});
test('guest cannot delete admin menu', function () {
    $menu = Menu::factory()->create();

    $response = $this->delete(
        route('admin.menus.destroy', $menu),
    );

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'deleted_at' => null,
    ]);
});
test('guest cannot restore admin menu', function () {
    $menu = Menu::factory()->create();

    $menu->delete();

    $response = $this->patch(
        route('admin.menus.restore', $menu),
    );

    $response->assertRedirect(route('login'));

    $this->assertSoftDeleted('menus', [
        'id' => $menu->id,
    ]);
});
test('authenticated user without menu permission cannot access admin menus', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'));

    $response->assertForbidden();
});
test('authenticated user without menu permission cannot store admin menu', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'unauthorized-menu',
            'label' => 'Unauthorized Menu',
            'type' => 'url',
            'url' => '/unauthorized',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('menus', [
        'name' => 'unauthorized-menu',
    ]);
});
test('authenticated user without menu permission cannot update admin menu', function () {
    $user = User::factory()->create();

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => '/unauthorized',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertForbidden();

    $menu->refresh();

    expect($menu->url)->toBe('/original');
});
test('authenticated user without menu permission cannot delete admin menu', function () {
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
test('authenticated user without menu permission cannot restore admin menu', function () {
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
test('admin menu update cannot change deleted at', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => '/original',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'deleted_at' => now()->toDateTimeString(),
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu->refresh();

    expect($menu->deleted_at)->toBeNull();
    expect($menu->url)->toBe('https://example.com/updated');
});
test('admin menu store cannot set deleted at', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'store-deleted-at-menu',
            'label' => 'Store Deleted At Menu',
            'type' => 'url',
            'url' => 'https://example.com/store-deleted-at',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'deleted_at' => now()->toDateTimeString(),
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu = Menu::query()
        ->where('name', 'store-deleted-at-menu')
        ->firstOrFail();

    expect($menu->deleted_at)->toBeNull();
});
test('admin menu store cannot set uuid', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $requestedUuid = '00000000-0000-0000-0000-000000000000';

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'store-custom-uuid-menu',
            'label' => 'Store Custom UUID Menu',
            'type' => 'url',
            'url' => 'https://example.com/store-custom-uuid',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'uuid' => $requestedUuid,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu = Menu::query()
        ->where('name', 'store-custom-uuid-menu')
        ->firstOrFail();

    expect($menu->uuid)->not->toBe($requestedUuid);
});
test('admin menu store cannot set id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $requestedId = 999999;

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'store-custom-id-menu',
            'label' => 'Store Custom ID Menu',
            'type' => 'url',
            'url' => 'https://example.com/store-custom-id',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'id' => $requestedId,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $menu = Menu::query()
        ->where('name', 'store-custom-id-menu')
        ->firstOrFail();

    expect($menu->id)->not->toBe($requestedId);
});
test('admin menu store rejects non existent parent id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-parent-menu',
            'label' => 'Invalid Parent Menu',
            'type' => 'url',
            'url' => '/invalid-parent',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
            'parent_id' => 999999,
        ]);

    $response->assertSessionHasErrors('parent_id');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-parent-menu',
    ]);
});

test('admin menu store rejects dangerous url scheme', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'dangerous-url-menu',
            'label' => 'Dangerous URL Menu',
            'type' => 'url',
            'url' => 'javascript:alert(document.cookie)',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('url');

    $this->assertDatabaseMissing('menus', [
        'name' => 'dangerous-url-menu',
    ]);
});
test('admin menu update rejects dangerous url scheme', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'url',
        'url' => 'https://example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'javascript:alert(document.cookie)',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('url');

    $menu->refresh();

    expect($menu->url)->toBe('https://example.com');
});
test('admin menu store rejects non existent route name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-route-menu',
            'label' => 'Invalid Route Menu',
            'type' => 'route',
            'route_name' => 'route.this.does.not.exist',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-route-menu',
    ]);
});

test('admin menu update rejects non existent route name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'route',
        'route_name' => 'admin.menus.index',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'route',
            'route_name' => 'route.this.does.not.exist',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $menu->refresh();

    expect($menu->route_name)->toBe('admin.menus.index');
});

test('admin menu store rejects invalid route name format', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'invalid-route-format-menu',
            'label' => 'Invalid Route Format Menu',
            'type' => 'route',
            'route_name' => '../../etc/passwd',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $this->assertDatabaseMissing('menus', [
        'name' => 'invalid-route-format-menu',
    ]);
});
test('admin menu update rejects invalid route name format', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'type' => 'route',
        'route_name' => 'admin.menus.index',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => $menu->name,
            'label' => $menu->label,
            'type' => 'route',
            'route_name' => '../../etc/passwd',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('route_name');

    $menu->refresh();

    expect($menu->route_name)->toBe('admin.menus.index');
});
test('admin menu store rejects invalid name format', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'Menu Utama !!!',
            'label' => 'Menu Utama',
            'type' => 'url',
            'url' => 'https://example.com/utama',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('menus', [
        'name' => 'Menu Utama !!!',
    ]);
});

test('admin menu store casts sort order from http input to integer', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'http-sort-order-menu',
            'label' => 'HTTP Sort Order Menu',
            'type' => 'url',
            'url' => 'https://example.com/menu',
            'target' => '_self',
            'icon' => 'circle',
            'sort_order' => '10',
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'name' => 'http-sort-order-menu',
        'sort_order' => 10,
    ]);
});

test('admin menu store casts active status from http input to boolean', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'http-active-menu',
            'label' => 'HTTP Active Menu',
            'type' => 'url',
            'url' => 'https://example.com/menu',
            'target' => '_self',
            'icon' => 'circle',
            'sort_order' => '10',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('admin.menus.index'));

    $this->assertDatabaseHas('menus', [
        'name' => 'http-active-menu',
        'is_active' => true,
    ]);
});

test('admin menu update rejects invalid name format', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'type' => 'url',
        'url' => 'https://example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'Menu Utama !!!',
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasErrors('name');

    $menu->refresh();

    expect($menu->name)->toBe('main-menu');
});
test('admin menu update allows keeping the existing name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'type' => 'url',
        'url' => 'https://example.com',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'main-menu',
            'label' => $menu->label,
            'type' => 'url',
            'url' => 'https://example.com/updated',
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ]);

    $response->assertSessionHasNoErrors();

    $response->assertRedirect();

    $menu->refresh();

    expect($menu->name)->toBe('main-menu')
        ->and($menu->url)->toBe('https://example.com/updated');
});
test('admin menu create page contains menu store form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<form action="' . route('admin.menus.store') . '" method="POST">',
            false,
        )
        ->assertSee(
            '<input type="hidden" name="_token"',
            false,
        );
});
test('admin menu create page contains save button', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<button type="submit">Simpan</button>',
            false,
        );
});

test('admin menu create page places save button inside menu store form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<form action="' . route('admin.menus.store') . '" method="POST">',
            false,
        )
        ->assertSee(
            '<button type="submit">Simpan</button>',
            false,
        );
});

test('admin menu create page preserves old input after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->withSession([
            '_old_input' => [
                'name' => 'main-menu',
                'label' => 'Menu Utama',
            ],
        ])
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee('value="main-menu"', false)
        ->assertSee('value="Menu Utama"', false);
});

test('admin menu create page preserves old type selection after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'type' => 'url',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<option value="url" selected>',
            false,
        );
});

test('admin menu create page preserves old url after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'type' => 'url',
            'url' => 'https://example.com/news',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            'value="https://example.com/news"',
            false,
        );
});

test('admin menu create page preserves old route name after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'type' => 'route',
            'route_name' => 'dashboard',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            'value="dashboard"',
            false,
        );
});

test('admin menu create page preserves old target selection after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'target' => '_blank',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<option value="_blank" selected>',
            false,
        );
});

test('admin menu create page preserves old icon after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'icon' => 'heroicon-o-home',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            'value="heroicon-o-home"',
            false,
        );
});

test('admin menu create page preserves old sort order after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'sort_order' => 25,
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            'value="25"',
            false,
        );
});

test('admin menu create page preserves old active status selection after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->withSession([
        '_old_input' => [
            'is_active' => '0',
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<option value="0" selected>',
            false,
        );
});

test('admin menu create page preserves old parent selection after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $parentMenu = Menu::factory()->create([
        'label' => 'Unique Parent Menu For Old Input Test',
    ]);

    $response = $this->withSession([
        '_old_input' => [
            'parent_id' => $parentMenu->id,
        ],
    ])
        ->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee(
            '<option value="' . $parentMenu->id . '" selected',
            false,
        )
        ->assertSee(
            'Unique Parent Menu For Old Input Test',
            false,
        );
});

test('admin menu create page displays validation errors after failed submission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->from(route('admin.menus.create'))
        ->post(route('admin.menus.store'), [
            'name' => '',
            'label' => 'Menu Utama',
            'type' => 'route',
            'route_name' => 'dashboard',
        ]);

    $response->assertRedirect(route('admin.menus.create'));

    $response = $this->actingAs($user)
        ->get(route('admin.menus.create'));

    $response->assertOk()
        ->assertSee('The name field is required.', false);
});

function actingAsMenuViewer(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo('menus.view');

    return $user;
}

test('admin menu edit page is accessible', function () {

    $user = actingAsMenuViewer();

    $user->givePermissionTo('menus.update');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response->assertOk()
        ->assertViewIs('admin.menus.edit')
        ->assertViewHas('menu', $menu);
});

it('admin menu edit page contains menu update form', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_blank',
        'icon' => 'home',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('<form', false)
        ->assertSee(
            'action="' . route('admin.menus.update', $menu) . '"',
            false
        )
        ->assertSee('method="POST"', false)
        ->assertSee('name="_method"', false)
        ->assertSee('value="PATCH"', false);
});

it('admin menu edit page contains all menu form fields', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_blank',
        'icon' => 'home',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('name="name"', false)
        ->assertSee('name="label"', false)
        ->assertSee('name="type"', false)
        ->assertSee('name="url"', false)
        ->assertSee('name="route_name"', false)
        ->assertSee('name="target"', false)
        ->assertSee('name="icon"', false)
        ->assertSee('name="sort_order"', false)
        ->assertSee('name="is_active"', false)
        ->assertSee('name="parent_id"', false);
});

it('admin menu edit page displays existing menu values', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_blank',
        'icon' => 'home',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('value="main-menu"', false)
        ->assertSee('value="Menu Utama"', false)
        ->assertSee('value="https://example.com"', false)
        ->assertSee('value="_blank"', false)
        ->assertSee('value="home"', false)
        ->assertSee('value="10"', false);
});

it('admin menu edit page selects the existing menu type', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'route',
        'route_name' => 'admin.dashboard',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee(
            '<option value="route" selected',
            false
        );
});

it('admin menu edit page selects the existing target', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_blank',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee(
            '<option value="_blank" selected',
            false
        );
});

it('admin menu edit page displays the existing route name', function () {
    $user = actingAsMenuViewer();
    $menu = Menu::factory()->create([
        'name' => 'dashboard-menu',
        'label' => 'Dashboard',
        'type' => 'route',
        'route_name' => 'admin.dashboard',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee(
            'value="admin.dashboard"',
            false
        );
});

it('admin menu edit page displays the existing parent menu', function () {
    $user = actingAsMenuViewer();
    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $menu = Menu::factory()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'parent_id' => $parent->id,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('Parent Menu')
        ->assertSee(
            '<option value="' . $parent->id . '" selected',
            false
        );
});

it('admin menu edit page provides available parent menus', function () {
    $user = actingAsMenuViewer();
    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('Parent Menu');
});

it('admin menu edit page does not provide soft deleted parent menus', function () {
    $user = actingAsMenuViewer();
    $deletedParent = Menu::factory()->create([
        'name' => 'deleted-parent',
        'label' => 'Deleted Parent',
    ]);

    $deletedParent->delete();

    $menu = Menu::factory()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertDontSee('Deleted Parent');
});

it('admin menu edit page does not allow the menu itself as parent', function () {

    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertDontSee(
            '<option value="' . $menu->id . '"',
            false
        );
});

it('admin menu edit page displays the active status correctly', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee(
            'value="1" selected',
            false
        );
});

it('admin menu edit page contains save button inside update form', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response
        ->assertOk()
        ->assertSee('Simpan', false);
});

test('admin menu index provides edit action for each active menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'menus.view',
        'menus.update',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'));

    $response->assertOk()
        ->assertSee(
            route('admin.menus.edit', $menu),
            false
        );
});

test('admin menu index provides delete action for each menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'menus.view',
        'menus.delete',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'));

    $response->assertOk()
        ->assertSee(
            'action="' . route('admin.menus.destroy', $menu) . '"',
            false
        );
});

test('admin menu index provides create menu link', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'menus.view',
        'menus.create',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'));

    $response->assertOk()
        ->assertSee(
            route('admin.menus.create'),
            false
        );
});

test('admin can view trashed menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertSee('Menu Terhapus');
});

test('admin menu trash only displays trashed menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $activeMenu = Menu::factory()->create([
        'name' => 'active-menu',
        'label' => 'Menu Aktif',
    ]);

    $deletedMenu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $deletedMenu->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertSee('Menu Terhapus')
        ->assertDontSee('Menu Aktif');
});

test('admin menu trash provides restore action for trashed menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'menus.view',
        'menus.restore',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertSee(
            'action="' . route('admin.menus.restore', $menu) . '"',
            false
        );
});

test('admin menu trash hides restore action without restore permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertDontSee(
            'action="' . route('admin.menus.restore', $menu) . '"',
            false
        );
});

test('admin menu trash restore form uses patch method', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'menus.view',
        'menus.restore',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertSee(
            '<input type="hidden" name="_method" value="PATCH"',
            false
        );
});

test('admin can restore a trashed menu from trash', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.restore');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    expect($menu->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertRedirect(route('admin.menus.index'));

    expect($menu->fresh()->trashed())->toBeFalse();
});

test('admin cannot restore an active menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.restore');

    $menu = Menu::factory()->create([
        'name' => 'active-menu',
        'label' => 'Menu Aktif',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertNotFound();

    expect($menu->fresh()->trashed())->toBeFalse();
});

test('admin sees empty state when menu trash is empty', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertOk()
        ->assertSee('Tidak ada menu terhapus');
});
test('user without menu view permission cannot view menu trash', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('admin.menus.trash'));

    $response->assertForbidden();
});

test('user without menu restore permission cannot restore a menu', function () {
    $user = User::factory()->create();

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertForbidden();

    expect($menu->fresh()->trashed())->toBeTrue();
});

it('admin menu create page does not provide soft deleted parent menus', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.create');

    $deletedParent = Menu::factory()->create([
        'name' => 'deleted-parent',
        'label' => 'Deleted Parent',
    ]);

    $deletedParent->delete();

    $response = $this->actingAs($user)->get(
        route('admin.menus.create')
    );

    $response
        ->assertOk()
        ->assertDontSee('Deleted Parent');
});

test('admin can restore a trashed child menu with its active parent', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.restore');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
    ]);

    $menu = Menu::factory()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'parent_id' => $parent->id,
    ]);

    $menu->delete();

    expect($menu->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu));

    $response->assertRedirect(route('admin.menus.index'));

    $restoredMenu = $menu->fresh();

    expect($restoredMenu->trashed())->toBeFalse()
        ->and($restoredMenu->parent_id)->toBe($parent->id);
});

test('admin cannot edit a trashed menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)->get(
        route('admin.menus.edit', $menu)
    );

    $response->assertNotFound();
});

test('admin cannot update a trashed menu', function () {
    $user = actingAsMenuViewer();

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'updated-menu',
            'label' => 'Updated Menu',
            'type' => 'url',
            'url' => 'https://example.com',
            'parent_id' => null,
            'icon' => null,
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

    $response->assertNotFound();

    $deletedMenu = Menu::withTrashed()->findOrFail($menu->id);

    expect($deletedMenu->trashed())->toBeTrue()
        ->and($deletedMenu->name)->toBe('deleted-menu')
        ->and($deletedMenu->label)->toBe('Menu Terhapus');
});

test('admin cannot delete a trashed menu again', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.delete');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $deletedAt = $menu->fresh()->deleted_at;

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response->assertNotFound();

    $menuAfterRequest = Menu::withTrashed()->findOrFail($menu->id);

    expect($menuAfterRequest->trashed())->toBeTrue()
        ->and($menuAfterRequest->deleted_at)->toEqual($deletedAt);
});

test('admin menu index renders nested menu hierarchy', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = \App\Models\Menu::factory()->create([
        'name' => 'services',
        'label' => 'Layanan',
        'sort_order' => 10,
    ]);

    $child = \App\Models\Menu::factory()->create([
        'parent_id' => $parent->id,
        'name' => 'library',
        'label' => 'Perpustakaan',
        'sort_order' => 10,
    ]);

    $grandchild = \App\Models\Menu::factory()->create([
        'parent_id' => $child->id,
        'name' => 'digital-library',
        'label' => 'Perpustakaan Digital',
        'sort_order' => 10,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee(
        '<ul data-menu-level="1">',
        false,
    );

    $response->assertSee(
        '<ul data-menu-level="2">',
        false,
    );

    $response->assertSee('Layanan');
    $response->assertSee('Perpustakaan');
    $response->assertSee('Perpustakaan Digital');
});

// test('admin menu index eagerly loads children for every hierarchy level', function () {
//     $user = actingAsMenuViewer();

//     $parent = Menu::factory()->create([
//         'name' => 'parent-menu',
//         'label' => 'Parent Menu',
//         'parent_id' => null,
//     ]);

//     $child = Menu::factory()->create([
//         'name' => 'child-menu',
//         'label' => 'Child Menu',
//         'parent_id' => $parent->id,
//     ]);

//     Menu::factory()->create([
//         'name' => 'grandchild-menu',
//         'label' => 'Grandchild Menu',
//         'parent_id' => $child->id,
//     ]);

//     $response = $this->actingAs($user)
//         ->get(route('admin.menus.index'))
//         ->assertOk();

//     $menus = $response->viewData('menus');

//     $parentMenu = $menus->firstWhere('id', $parent->id);

//     expect($parentMenu)->not->toBeNull()
//         ->and($parentMenu->relationLoaded('children'))->toBeTrue();

//     $childMenu = $parentMenu->children->firstWhere('id', $child->id);

//     expect($childMenu)->not->toBeNull()
//         ->and($childMenu->relationLoaded('children'))->toBeTrue();

//     $grandchildMenu = $childMenu->children
//         ->firstWhere('name', 'grandchild-menu');

//     expect($grandchildMenu)->not->toBeNull()
//         ->and($grandchildMenu->relationLoaded('children'))->toBeTrue();
// });

test('admin menu index does not lazy load children at any hierarchy level', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $parent = Menu::factory()->create([
        'name' => 'parent-menu',
        'label' => 'Parent Menu',
        'parent_id' => null,
    ]);

    $child = Menu::factory()->create([
        'name' => 'child-menu',
        'label' => 'Child Menu',
        'parent_id' => $parent->id,
    ]);

    Menu::factory()->create([
        'name' => 'grandchild-menu',
        'label' => 'Grandchild Menu',
        'parent_id' => $child->id,
    ]);

    Model::preventLazyLoading();

    try {
        $response = $this->actingAs($user)
            ->get(route('admin.menus.index'))
            ->assertOk();

        expect($response->viewData('menus'))->not->toBeEmpty();
    } finally {
        Model::preventLazyLoading(false);
    }
});

test('admin menu index contains link to menu trash', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $response = $this->actingAs($user)
        ->get(route('admin.menus.index'))
        ->assertOk();

    $response->assertSee('Trash');
    $response->assertSee(
        route('admin.menus.trash'),
        false,
    );
});

test('admin menu force delete requires force delete permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menu->delete();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.force-delete', $menu));

    $response->assertForbidden();

    expect(Menu::withTrashed()->find($menu->id))->not->toBeNull();
});

test('admin cannot force delete an active menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.force-delete');

    $menu = Menu::factory()->create([
        'name' => 'active-menu',
        'label' => 'Menu Aktif',
    ]);

    $this->actingAs($user)
        ->delete(route('admin.menus.force-delete', $menu))
        ->assertNotFound();

    expect(Menu::find($menu->id))->not->toBeNull();
});

test('admin can force delete a trashed menu', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.force-delete');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menuId = $menu->id;

    $menu->delete();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.force-delete', $menu));

    $response->assertRedirect(route('admin.menus.trash'));

    expect(Menu::withTrashed()->find($menuId))->toBeNull();
});

test('admin menu force delete uses uuid route binding', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.force-delete');

    $menu = Menu::factory()->create([
        'name' => 'deleted-menu',
        'label' => 'Menu Terhapus',
    ]);

    $menuId = $menu->id;

    $menu->delete();

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.force-delete', [
            'menu' => $menu->uuid,
        ]));

    $response->assertRedirect(route('admin.menus.trash'));

    expect(Menu::withTrashed()->find($menuId))->toBeNull();
});

test('admin menu permissions are granular', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('menus.view');

    $menu = Menu::factory()->create([
        'name' => 'main-menu',
        'label' => 'Menu Utama',
    ]);

    $this->actingAs($user)
        ->get(route('admin.menus.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.menus.edit', $menu))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('admin.menus.store'), [
            'name' => 'new-menu',
            'label' => 'New Menu',
            'type' => 'url',
            'url' => 'https://example.com',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('admin.menus.update', $menu), [
            'name' => 'updated-menu',
            'label' => 'Updated Menu',
            'type' => 'url',
            'url' => 'https://example.com',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu))
        ->assertForbidden();

    $menu->delete();

    $this->actingAs($user)
        ->patch(route('admin.menus.restore', $menu))
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('admin.menus.force-delete', $menu))
        ->assertForbidden();
});
