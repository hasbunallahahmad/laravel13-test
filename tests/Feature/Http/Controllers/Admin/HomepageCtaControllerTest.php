<?php

declare(strict_types=1);

use App\Models\HomepageCta;
use App\Models\HomepageCtaButton;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('homepage cta admin routes are registered', function () {
    $expectedRoutes = [
        'admin.homepage-ctas.index',
        'admin.homepage-ctas.create',
        'admin.homepage-ctas.store',
        'admin.homepage-ctas.trash',
        'admin.homepage-ctas.edit',
        'admin.homepage-ctas.update',
        'admin.homepage-ctas.destroy',
        'admin.homepage-ctas.restore',
        'admin.homepage-ctas.force-delete',
    ];

    foreach ($expectedRoutes as $routeName) {
        expect(Route::has($routeName))
            ->toBeTrue("Route [{$routeName}] must be registered.");
    }
});

test('homepage cta admin routes require authentication', function () {
    $routeNames = [
        'admin.homepage-ctas.index',
        'admin.homepage-ctas.create',
        'admin.homepage-ctas.store',
        'admin.homepage-ctas.trash',
        'admin.homepage-ctas.edit',
        'admin.homepage-ctas.update',
        'admin.homepage-ctas.destroy',
        'admin.homepage-ctas.restore',
        'admin.homepage-ctas.force-delete',
    ];

    foreach ($routeNames as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())
            ->toContain('auth');
    }
});

test('homepage cta admin routes enforce granular permissions', function () {
    $expectedPermissions = [
        'admin.homepage-ctas.index' => 'homepage-ctas.view',
        'admin.homepage-ctas.create' => 'homepage-ctas.create',
        'admin.homepage-ctas.store' => 'homepage-ctas.create',
        'admin.homepage-ctas.trash' => 'homepage-ctas.view',
        'admin.homepage-ctas.edit' => 'homepage-ctas.update',
        'admin.homepage-ctas.update' => 'homepage-ctas.update',
        'admin.homepage-ctas.destroy' => 'homepage-ctas.delete',
        'admin.homepage-ctas.restore' => 'homepage-ctas.restore',
        'admin.homepage-ctas.force-delete' => 'homepage-ctas.force-delete',
    ];

    foreach ($expectedPermissions as $routeName => $permission) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())
            ->toContain("permission:{$permission}");
    }
});

test('homepage cta admin index redirects guests to login', function () {
    $this->get(route('admin.homepage-ctas.index'))
        ->assertRedirect(route('login'));
});

test('homepage cta admin routes forbid authenticated users without permissions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertForbidden();
});

test('user with view permission can access homepage cta index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk();
});

test('homepage cta index displays active and inactive records but excludes soft deleted records', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    HomepageCta::create([
        'title' => 'CTA Aktif',
        'description' => 'CTA yang sedang aktif.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    HomepageCta::create([
        'title' => 'CTA Nonaktif',
        'description' => 'CTA yang sedang nonaktif.',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    $deletedCta = HomepageCta::create([
        'title' => 'CTA Terhapus',
        'description' => 'CTA yang sudah dihapus.',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $deletedCta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSee('CTA Aktif')
        ->assertSee('CTA Nonaktif')
        ->assertDontSee('CTA Terhapus');
});

test('homepage cta index displays records ordered by sort order', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    HomepageCta::create([
        'title' => 'CTA Ketiga',
        'sort_order' => 30,
        'is_active' => true,
    ]);

    HomepageCta::create([
        'title' => 'CTA Pertama',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    HomepageCta::create([
        'title' => 'CTA Kedua',
        'sort_order' => 20,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'CTA Pertama',
            'CTA Kedua',
            'CTA Ketiga',
        ]);
});

test('user with create permission can access homepage cta create page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertOk();
});

test('user with view permission cannot access homepage cta create page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertForbidden();
});

test('homepage cta create page displays the creation form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertOk()
        ->assertSee('Tambah Homepage CTA')
        ->assertSee('Simpan', false)
        ->assertSee('Judul CTA')
        ->assertSee('Deskripsi');
});

test('homepage cta create page displays status and sort order fields', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertOk()
        ->assertSee('Urutan')
        ->assertSee('Aktif')
        ->assertSee('name="sort_order"', false)
        ->assertSee('name="is_active"', false);
});

test('homepage cta store rejects invalid data', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [])
        ->assertSessionHasErrors([
            'title',
            'buttons',
        ]);
});

test('user with create permission can store homepage cta with buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $response = $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'Jelajahi Layanan Kami',
            'description' => 'Temukan berbagai layanan Dinas Arpus.',
            'sort_order' => 10,
            'is_active' => '1',
            'buttons' => [
                [
                    'label' => 'Jelajahi Layanan',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('homepage_ctas', [
        'title' => 'Jelajahi Layanan Kami',
        'description' => 'Temukan berbagai layanan Dinas Arpus.',
        'sort_order' => 10,
        'is_active' => true,
        'deleted_at' => null,
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'label' => 'Jelajahi Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
        'deleted_at' => null,
    ]);
});

test('user without create permission cannot store homepage cta', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'CTA Tanpa Izin',
            'description' => 'Pengujian otorisasi.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Lihat Layanan',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('homepage_ctas', [
        'title' => 'CTA Tanpa Izin',
    ]);
});

test('homepage cta store rejects unsafe button destinations', function (string $url) {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'CTA Tidak Aman',
            'description' => 'Pengujian keamanan URL.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Klik di sini',
                    'type' => 'url',
                    'url' => $url,
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.url');

    $this->assertDatabaseMissing('homepage_ctas', [
        'title' => 'CTA Tidak Aman',
    ]);
})->with([
    'javascript scheme' => 'javascript:alert(1)',
    'data scheme' => 'data:text/html,<script>alert(1)</script>',
    'protocol relative URL' => '//evil.example',
    'admin path' => '/admin/users',
    'credentials in URL' => 'https://user:password@example.com',
]);

test('homepage cta store rejects invalid route destinations', function (string $routeName) {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'CTA Route Tidak Valid',
            'description' => 'Pengujian keamanan route.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Buka Halaman',
                    'type' => 'route',
                    'route_name' => $routeName,
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.route_name');

    $this->assertDatabaseMissing('homepage_ctas', [
        'title' => 'CTA Route Tidak Valid',
    ]);
})->with([
    'unknown route' => 'route.tidak.ada',
    'admin dashboard' => 'admin.dashboard',
    'admin settings' => 'admin.settings.index',
]);

test('homepage cta store rejects invalid button fields', function (array $button, string $errorField) {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'CTA Invalid Button',
            'description' => 'Pengujian validasi tombol.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                array_merge([
                    'label' => 'Tombol Pengujian',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ], $button),
            ],
        ])
        ->assertSessionHasErrors($errorField);

    $this->assertDatabaseMissing('homepage_ctas', [
        'title' => 'CTA Invalid Button',
    ]);
})->with([
    'empty label' => [
        ['label' => ''],
        'buttons.0.label',
    ],
    'invalid type' => [
        ['type' => 'script'],
        'buttons.0.type',
    ],
    'invalid target' => [
        ['target' => '_parent'],
        'buttons.0.target',
    ],
    'negative button order' => [
        ['sort_order' => -1],
        'buttons.0.sort_order',
    ],
]);

test('homepage cta store rejects invalid parent fields', function (array $data, string $errorField) {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $payload = [
        'title' => 'CTA Pengujian',
        'description' => 'Deskripsi pengujian.',
        'sort_order' => 0,
        'is_active' => true,
        'buttons' => [
            [
                'label' => 'Lihat Layanan',
                'type' => 'url',
                'url' => '/layanan',
                'target' => '_self',
                'sort_order' => 0,
                'is_active' => true,
            ],
        ],
    ];

    $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), array_merge($payload, $data))
        ->assertSessionHasErrors($errorField);

    $this->assertDatabaseMissing('homepage_ctas', [
        'title' => 'CTA Pengujian',
    ]);
})->with([
    'empty title' => [
        ['title' => ''],
        'title',
    ],
    'title exceeds maximum length' => [
        ['title' => str_repeat('A', 256)],
        'title',
    ],
    'description exceeds maximum length' => [
        ['description' => str_repeat('A', 10001)],
        'description',
    ],
    'negative parent order' => [
        ['sort_order' => -1],
        'sort_order',
    ],
    'invalid active status' => [
        ['is_active' => 'invalid'],
        'is_active',
    ],
]);

test('homepage cta create page displays button fields', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertOk()
        ->assertSee('buttons[0][label]', false)
        ->assertSee('buttons[0][type]', false)
        ->assertSee('buttons[0][url]', false)
        ->assertSee('buttons[0][route_name]', false)
        ->assertSee('buttons[0][target]', false)
        ->assertSee('buttons[0][sort_order]', false)
        ->assertSee('buttons[0][is_active]', false);
});

test('homepage cta create page supports adding multiple buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.create'))
        ->assertOk()
        ->assertSee('Tambah Tombol')
        ->assertSee('homepage-cta-buttons')
        ->assertSee('data-button-index="0"', false);
});

test('user with create permission can store homepage cta with multiple buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.create');

    $response = $this->actingAs($user)
        ->post(route('admin.homepage-ctas.store'), [
            'title' => 'CTA Multi Tombol',
            'description' => 'CTA dengan beberapa pilihan navigasi.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Jelajahi Layanan',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
                [
                    'label' => 'Beranda',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_blank',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.index'))
        ->assertSessionHas('success');

    $cta = HomepageCta::query()
        ->where('title', 'CTA Multi Tombol')
        ->firstOrFail();

    expect($cta->buttons)->toHaveCount(2);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $cta->id,
        'label' => 'Jelajahi Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $cta->id,
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
    ]);
});

test('user with update permission can access homepage cta edit page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Pengujian Edit',
        'description' => 'Pengujian halaman edit.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta->uuid))
        ->assertOk();
});

test('edit page displays homepage cta details and its buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan Perpustakaan',
        'description' => 'Akses layanan perpustakaan digital.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $cta->buttons()->create([
        'label' => 'Lihat Koleksi',
        'type' => 'url',
        'url' => '/koleksi',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $cta->buttons()->create([
        'label' => 'Baca Berita',
        'type' => 'url',
        'url' => '/berita',
        'target' => '_blank',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta))
        ->assertOk()
        ->assertSee('CTA Layanan Perpustakaan')
        ->assertSee('Akses layanan perpustakaan digital.')
        ->assertSee('Lihat Koleksi')
        ->assertSee('Baca Berita');
});

test('user without update permission cannot access homepage cta edit page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Pengujian Permission',
        'description' => 'Pengujian akses edit.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta))
        ->assertForbidden();
});

test('homepage cta edit route does not resolve numeric id', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA UUID Binding',
        'description' => 'Pengujian UUID.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/homepage-ctas/'.$cta->id.'/edit')
        ->assertNotFound();
});

test('soft deleted homepage cta cannot access edit page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Pengujian soft delete.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta->uuid))
        ->assertNotFound();
});

test('edit page displays homepage cta form with existing button data', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan Perpustakaan',
        'description' => 'Akses layanan perpustakaan digital.',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Koleksi',
        'type' => 'url',
        'url' => '/koleksi',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta))
        ->assertOk()
        ->assertSee('value="CTA Layanan Perpustakaan"', false)
        ->assertSee('Akses layanan perpustakaan digital.')
        ->assertSee('name="buttons[0][uuid]"', false)
        ->assertSee($button->uuid)
        ->assertSee('Lihat Koleksi')
        ->assertSee('/koleksi')
        ->assertSee('Simpan Perubahan');
});

test('user with update permission can update homepage cta and its buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
        'description' => 'Deskripsi lama.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Lama',
        'type' => 'url',
        'url' => '/lama',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Baru',
            'description' => 'Deskripsi baru.',
            'sort_order' => 2,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Lihat Koleksi',
                    'type' => 'url',
                    'url' => '/koleksi',
                    'target' => '_blank',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $cta->id,
        'title' => 'CTA Baru',
        'description' => 'Deskripsi baru.',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $button->id,
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Koleksi',
        'url' => '/koleksi',
        'target' => '_blank',
        'sort_order' => 1,
        'is_active' => true,
    ]);
});

test('updating homepage cta rejects unsafe button destination', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Aman',
        'description' => 'Deskripsi aman.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Koleksi',
        'type' => 'url',
        'url' => '/koleksi',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Aman',
            'description' => 'Deskripsi aman.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Lihat Koleksi',
                    'type' => 'url',
                    'url' => 'javascript:alert(1)',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors('buttons.0.url');

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $button->id,
        'url' => '/koleksi',
    ]);
});

test('updating homepage cta rejects unauthorized route destination', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Aman',
        'description' => 'Deskripsi aman.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Aman',
            'description' => 'Deskripsi aman.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Dashboard Admin',
                    'type' => 'route',
                    'route_name' => 'admin.dashboard',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors('buttons.0.route_name');

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $button->id,
        'route_name' => 'home',
    ]);
});

test('updating homepage cta rejects button uuid belonging to another cta', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $ctaA = HomepageCta::query()->create([
        'title' => 'CTA A',
        'description' => 'Deskripsi CTA A.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $ctaB = HomepageCta::query()->create([
        'title' => 'CTA B',
        'description' => 'Deskripsi CTA B.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $buttonB = $ctaB->buttons()->create([
        'label' => 'Tombol CTA B',
        'type' => 'url',
        'url' => '/cta-b',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $ctaA))
        ->patch(route('admin.homepage-ctas.update', $ctaA), [
            'title' => 'CTA A Diubah',
            'description' => 'Deskripsi CTA A diubah.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $buttonB->uuid,
                    'label' => 'Tombol Dimanipulasi',
                    'type' => 'url',
                    'url' => '/manipulasi',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $ctaA))
        ->assertSessionHasErrors('buttons.0.uuid');

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $ctaA->id,
        'title' => 'CTA A',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $buttonB->id,
        'homepage_cta_id' => $ctaB->id,
        'label' => 'Tombol CTA B',
        'url' => '/cta-b',
    ]);
});

test('updating homepage cta rejects unknown button uuid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Aman',
        'description' => 'Deskripsi aman.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Dimanipulasi',
            'description' => 'Deskripsi dimanipulasi.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => '00000000-0000-4000-8000-000000000001',
                    'label' => 'Tombol Palsu',
                    'type' => 'url',
                    'url' => '/palsu',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors('buttons.0.uuid');

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $cta->id,
        'title' => 'CTA Aman',
    ]);

    $this->assertDatabaseCount('homepage_cta_buttons', 0);
});

test('updating homepage cta synchronizes existing new and omitted buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Sebelum Update',
        'description' => 'Deskripsi sebelum update.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $existingButton = $cta->buttons()->create([
        'label' => 'Tombol Lama',
        'type' => 'url',
        'url' => '/lama',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $omittedButton = $cta->buttons()->create([
        'label' => 'Tombol Dihapus',
        'type' => 'url',
        'url' => '/dihapus',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Setelah Update',
            'description' => 'Deskripsi setelah update.',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $existingButton->uuid,
                    'label' => 'Tombol Diperbarui',
                    'type' => 'url',
                    'url' => '/diperbarui',
                    'target' => '_blank',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
                [
                    'label' => 'Tombol Baru',
                    'type' => 'url',
                    'url' => '/baru',
                    'target' => '_self',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

    $response->assertRedirect(route('admin.homepage-ctas.index'));

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $existingButton->id,
        'label' => 'Tombol Diperbarui',
        'url' => '/diperbarui',
        'target' => '_blank',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $cta->id,
        'label' => 'Tombol Baru',
        'url' => '/baru',
        'deleted_at' => null,
    ]);

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $omittedButton->id,
    ]);

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $cta->id,
        'title' => 'CTA Setelah Update',
    ]);
});

test('updating homepage cta rejects empty buttons collection', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Aman',
        'description' => 'Deskripsi aman.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Koleksi',
        'type' => 'url',
        'url' => '/koleksi',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Dimanipulasi',
            'description' => 'Deskripsi dimanipulasi.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors('buttons');

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $cta->id,
        'title' => 'CTA Aman',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $button->id,
        'label' => 'Lihat Koleksi',
        'deleted_at' => null,
    ]);
});

test('updating homepage cta rejects duplicate button uuids', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Aman',
        'description' => 'Deskripsi aman.',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Asli',
        'type' => 'url',
        'url' => '/asli',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Aman',
            'description' => 'Deskripsi aman.',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Pertama',
                    'type' => 'url',
                    'url' => '/pertama',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Kedua',
                    'type' => 'url',
                    'url' => '/kedua',
                    'target' => '_self',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors([
            'buttons.0.uuid',
            'buttons.1.uuid',
        ]);

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $cta->id,
        'title' => 'CTA Aman',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $button->id,
        'label' => 'Tombol Asli',
        'url' => '/asli',
        'deleted_at' => null,
    ]);

    $this->assertDatabaseCount('homepage_cta_buttons', 1);
});

test('user without update permission cannot update homepage cta', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Beranda',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertForbidden();

    expect($cta->fresh()->title)->toBe('CTA Original');
});

test('updating soft deleted homepage cta is not allowed', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta->uuid), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Beranda',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertNotFound();

    expect(HomepageCta::withTrashed()->find($cta->id)->title)
        ->toBe('CTA Terhapus');
});

test('updating homepage cta does not resolve numeric id', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', ['homepageCta' => $cta->id]), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'label' => 'Beranda',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertNotFound();

    expect($cta->fresh()->title)->toBe('CTA Original');
});

test('updating homepage cta rejects invalid button label', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => '',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.label');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->label)->toBe('Tombol Original');
});

test('updating homepage cta rejects invalid button type', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'javascript',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.type');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->type)->toBe('route');
});

test('updating homepage cta rejects url button without destination', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'url',
                    'url' => '',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.url');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->url)->toBe('https://example.com');
});

test('updating homepage cta rejects route button without destination', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => '',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.route_name');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->route_name)->toBe('home');
});

test('updating homepage cta rejects invalid button target', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_top',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.target');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->target)->toBe('_self');
});

test('updating homepage cta rejects negative button sort order', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => -1,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.sort_order');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->sort_order)->toBe(0);
});

test('updating homepage cta rejects invalid button active status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => 'invalid',
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.is_active');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->is_active)->toBeTrue();
});

test('updating homepage cta rejects negative parent sort order', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => -1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('sort_order');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($cta->fresh()->sort_order)->toBe(0);
});

test('updating homepage cta rejects invalid parent active status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => 'invalid',
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('is_active');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($cta->fresh()->is_active)->toBeTrue();
});

test('updating homepage cta rejects empty parent title', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => '',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('title');

    expect($cta->fresh()->title)->toBe('CTA Original');
});

test('updating homepage cta rejects title exceeding maximum length', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => str_repeat('A', 256),
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('title');

    expect($cta->fresh()->title)->toBe('CTA Original');
});

test('updating homepage cta rejects description exceeding maximum length', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => str_repeat('A', 5001),
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('description');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($cta->fresh()->description)->toBe('Deskripsi original');
});

test('updating homepage cta accepts null description', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diperbarui',
            'description' => null,
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertRedirect(route('admin.homepage-ctas.index'));

    expect($cta->fresh()->title)->toBe('CTA Diperbarui')
        ->and($cta->fresh()->description)->toBeNull()
        ->and($button->fresh()->exists)->toBeTrue();
});

test('updating homepage cta rejects button label exceeding maximum length', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Original',
            'description' => 'Deskripsi original',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => str_repeat('A', 101),
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.label');

    expect($button->fresh()->label)->toBe('Tombol Original');
});

test('updating homepage cta rejects url exceeding maximum length', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'url',
        'url' => 'https://example.com',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Original',
            'description' => 'Deskripsi original',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'url',
                    'url' => 'https://example.com/'.str_repeat('a', 2030),
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.url');

    expect($button->fresh()->url)->toBe('https://example.com');
});

test('updating homepage cta rejects route name exceeding maximum length', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Original',
            'description' => 'Deskripsi original',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Original',
                    'type' => 'route',
                    'route_name' => str_repeat('a', 101),
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.route_name');

    expect($button->fresh()->route_name)->toBe('home');
});

test('updating homepage cta rejects malformed button uuid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => 'bukan-uuid-valid',
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.uuid');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and($button->fresh()->label)->toBe('Tombol Original');
});
test('updating homepage cta rejects soft deleted button uuid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Original',
        'description' => 'Deskripsi original',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Original',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button->delete();

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Diubah',
            'description' => 'Deskripsi baru',
            'sort_order' => 1,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Tombol Diubah',
                    'type' => 'route',
                    'route_name' => 'home',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('buttons.0.uuid');

    expect($cta->fresh()->title)->toBe('CTA Original')
        ->and(HomepageCtaButton::query()->find($button->id))->toBeNull()
        ->and(
            HomepageCtaButton::withTrashed()
                ->findOrFail($button->id)
                ->trashed()
        )->toBeTrue();
});

test('edit homepage cta page provides controls to add and remove buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.edit', $cta))
        ->assertOk()
        ->assertSee('Tambah Tombol')
        ->assertSee('Hapus Tombol')
        ->assertSee('add-homepage-cta-button')
        ->assertSee('homepage-cta-button-template')
        ->assertSee('remove-homepage-cta-button');
});

test('edit homepage cta page preserves newly added button input after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => 'CTA Layanan',
            'description' => 'Deskripsi layanan',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Lihat Layanan',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
                [
                    'label' => 'Tombol Baru',
                    'type' => 'url',
                    'url' => 'javascript:alert(1)',
                    'target' => '_self',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertRedirect(route('admin.homepage-ctas.edit', $cta))
        ->assertSessionHasErrors('buttons.1.url');

    $this->get(route('admin.homepage-ctas.edit', $cta))
        ->assertOk()
        ->assertSee('Tombol Baru')
        ->assertSee('buttons[1][label]', false)
        ->assertSee('javascript:alert(1)');
});
test('edit homepage cta page preserves existing button uuid after validation failure', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->from(route('admin.homepage-ctas.edit', $cta))
        ->patch(route('admin.homepage-ctas.update', $cta), [
            'title' => '',
            'description' => 'Deskripsi layanan',
            'sort_order' => 0,
            'is_active' => true,
            'buttons' => [
                [
                    'uuid' => $button->uuid,
                    'label' => 'Lihat Layanan',
                    'type' => 'url',
                    'url' => '/layanan',
                    'target' => '_self',
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            ],
        ])
        ->assertSessionHasErrors('title');

    $this->get(route('admin.homepage-ctas.edit', $cta))
        ->assertOk()
        ->assertSee('buttons[0][uuid]', false)
        ->assertSee('value="'.$button->uuid.'"', false);
});
test('user with delete permission can soft delete homepage cta and its buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.delete');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('admin.homepage-ctas.destroy', $cta))
        ->assertRedirect(route('admin.homepage-ctas.index'))
        ->assertSessionHas('success');

    expect(HomepageCta::query()->find($cta->id))->toBeNull()
        ->and(HomepageCta::withTrashed()->findOrFail($cta->id)->trashed())->toBeTrue()
        ->and(HomepageCtaButton::query()->find($button->id))->toBeNull()
        ->and(HomepageCtaButton::withTrashed()->findOrFail($button->id)->trashed())->toBeTrue();
});
test('user without delete permission cannot delete homepage cta', function () {
    $user = User::factory()->create();

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('admin.homepage-ctas.destroy', $cta))
        ->assertForbidden();

    expect(HomepageCta::query()->find($cta->id))->not->toBeNull()
        ->and($cta->fresh()->trashed())->toBeFalse();
});
test('user with view permission can access homepage cta trash page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk();
});
test('homepage cta trash page only displays soft deleted ctas', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $activeCta = HomepageCta::query()->create([
        'title' => 'CTA Aktif',
        'description' => 'CTA yang masih aktif',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $deletedCta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'CTA yang sudah dihapus',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $deletedCta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertSee('CTA Terhapus')
        ->assertDontSee('CTA Aktif');
});
test('homepage cta trash page displays soft deleted buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol Terhapus',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertSee('CTA Terhapus')
        ->assertSee('Tombol Terhapus');
});
test('user with restore permission can restore homepage cta', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.restore');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi layanan',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button->delete();
    $cta->delete();

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.restore', $cta->uuid))
        ->assertRedirect(route('admin.homepage-ctas.trash'))
        ->assertSessionHas('success');

    expect(HomepageCta::query()->find($cta->id))->not->toBeNull()
        ->and(HomepageCta::withTrashed()->findOrFail($cta->id)->trashed())->toBeFalse()
        ->and(HomepageCtaButton::query()->find($button->id))->toBeNull()
        ->and(
            HomepageCtaButton::withTrashed()
                ->findOrFail($button->id)
                ->trashed()
        )->toBeTrue();
});
test('user without restore permission cannot restore homepage cta', function () {
    $user = User::factory()->create();

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->patch(route('admin.homepage-ctas.restore', $cta->uuid))
        ->assertForbidden();

    expect(HomepageCta::query()->find($cta->id))->toBeNull()
        ->and(HomepageCta::withTrashed()->findOrFail($cta->id)->trashed())->toBeTrue();
});
test('user with force delete permission can permanently delete homepage cta and its buttons', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.force-delete');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus Permanen',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol CTA',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button->delete();
    $cta->delete();

    $this->actingAs($user)
        ->delete(route('admin.homepage-ctas.force-delete', $cta->uuid))
        ->assertRedirect(route('admin.homepage-ctas.trash'))
        ->assertSessionHas('success');

    expect(HomepageCta::withTrashed()->find($cta->id))->toBeNull()
        ->and(HomepageCtaButton::withTrashed()->find($button->id))->toBeNull();
});
test('user without force delete permission cannot permanently delete homepage cta', function () {
    $user = User::factory()->create();

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button = $cta->buttons()->create([
        'label' => 'Tombol CTA',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $button->delete();
    $cta->delete();

    $this->actingAs($user)
        ->delete(route('admin.homepage-ctas.force-delete', $cta->uuid))
        ->assertForbidden();

    expect(HomepageCta::withTrashed()->find($cta->id))->not->toBeNull()
        ->and(HomepageCtaButton::withTrashed()->find($button->id))->not->toBeNull();
});
test('user without view permission cannot access homepage cta trash page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertForbidden();
});
test('homepage cta trash page displays restore action for authorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'homepage-ctas.view',
        'homepage-ctas.restore',
    ]);

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertSee('Pulihkan')
        ->assertSee(route('admin.homepage-ctas.restore', $cta->uuid), false);
});
test('homepage cta trash page hides restore action from unauthorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertDontSee('Pulihkan')
        ->assertDontSee(
            route('admin.homepage-ctas.restore', $cta->uuid),
            false,
        );
});
test('homepage cta trash page displays force delete action for authorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'homepage-ctas.view',
        'homepage-ctas.force-delete',
    ]);

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertSee('Hapus Permanen')
        ->assertSee(
            route('admin.homepage-ctas.force-delete', $cta->uuid),
            false,
        );
});
test('homepage cta trash page hides force delete action from unauthorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Terhapus',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $cta->delete();

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertDontSee('Hapus Permanen')
        ->assertDontSee(
            route('admin.homepage-ctas.force-delete', $cta->uuid),
            false,
        );
});
test('homepage cta index page provides link to trash page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSee('Sampah')
        ->assertSee(route('admin.homepage-ctas.trash'), false);
});
test('homepage cta index page displays create action for authorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view', 'homepage-ctas.create');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSee('Tambah Homepage CTA')
        ->assertSee(route('admin.homepage-ctas.create'), false);
});
test('homepage cta index page hides create action from unauthorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertDontSee('Tambah Homepage CTA')
        ->assertDontSee(
            route('admin.homepage-ctas.create'),
            false,
        );
});
test('homepage cta index page displays edit action for authorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view', 'homepage-ctas.update');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSee('Edit')
        ->assertSee(route('admin.homepage-ctas.edit', $cta->uuid), false);
});
test('homepage cta index page hides edit action from unauthorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertDontSee('Edit')
        ->assertDontSee(
            route('admin.homepage-ctas.edit', $cta->uuid),
            false,
        );
});
test('homepage cta index page displays delete action for authorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view', 'homepage-ctas.delete');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertSee('Hapus')
        ->assertSee(route('admin.homepage-ctas.destroy', $cta->uuid), false)
        ->assertSee('name="_method" value="DELETE"', false);
});
test('homepage cta index page hides delete action from unauthorized user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $cta = HomepageCta::query()->create([
        'title' => 'CTA Layanan',
        'description' => 'Deskripsi CTA',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.index'))
        ->assertOk()
        ->assertDontSee('Hapus')
        ->assertDontSee(
            route('admin.homepage-ctas.destroy', $cta->uuid),
            false,
        );
});
test('homepage cta trash page provides link back to index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('homepage-ctas.view');

    $this->actingAs($user)
        ->get(route('admin.homepage-ctas.trash'))
        ->assertOk()
        ->assertSee('Kembali ke Homepage CTA')
        ->assertSee(route('admin.homepage-ctas.index'), false);
});
