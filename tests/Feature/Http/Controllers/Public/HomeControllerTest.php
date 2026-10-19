<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Menu;
use App\Models\Setting;

it('displays the public homepage', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertViewIs('public.home');
});

it('displays only published news on the homepage', function (): void {
    $publishedNews = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita yang Dipublikasikan',
        'slug' => 'berita-yang-dipublikasikan',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $publishedWithoutDate = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Tanpa Tanggal Publikasi',
        'slug' => 'berita-tanpa-tanggal-publikasi',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => null,
    ]);

    Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Draft',
        'status' => ContentStatus::DRAFT,
    ]);

    Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Terjadwal',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    Content::factory()->create([
        'type' => ContentType::ARTICLE,
        'title' => 'Artikel Bukan Berita',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $deletedNews = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita yang Dihapus',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $deletedNews->delete();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($publishedNews->title)
        ->assertSee($publishedWithoutDate->title)
        ->assertDontSee('Berita Draft')
        ->assertDontSee('Berita Terjadwal')
        ->assertDontSee('Artikel Bukan Berita')
        ->assertDontSee('Berita yang Dihapus');
});

it('displays only active non-deleted menus on the homepage', function (): void {
    $activeMenu = Menu::factory()->create([
        'label' => 'Profil Dinas',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    Menu::factory()->create([
        'label' => 'Menu Nonaktif',
        'is_active' => false,
        'sort_order' => 2,
    ]);

    $deletedMenu = Menu::factory()->create([
        'label' => 'Menu Terhapus',
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $deletedMenu->delete();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($activeMenu->label)
        ->assertDontSee('Menu Nonaktif')
        ->assertDontSee('Menu Terhapus');
});

it('displays public settings but does not expose private settings', function (): void {
    Setting::factory()->create([
        'group' => 'contact',
        'key' => 'public_contact_test',
        'value' => 'Kontak Publik Contoh',
        'type' => 'string',
        'is_public' => true,
    ]);

    Setting::factory()->private()->create([
        'group' => 'system',
        'key' => 'private_secret_test',
        'value' => 'RAHASIA-JANGAN-TAMPILKAN',
        'type' => 'string',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Kontak Publik Contoh')
        ->assertDontSee('RAHASIA-JANGAN-TAMPILKAN');
});

it('renders a valid public named route as a menu link', function (): void {
    Menu::factory()->route('home')->create([
        'label' => 'Beranda',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="' . route('home') . '"', false)
        ->assertSee('Beranda');
});

it('renders a valid HTTP URL as a menu link', function (): void {
    Menu::factory()->url('https://example.org/profil')->create([
        'label' => 'Profil Eksternal',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="https://example.org/profil"', false)
        ->assertSee('Profil Eksternal');
});

it('does not render unsafe or non-public menu destinations as links', function (): void {
    Menu::factory()->url('javascript:alert(1)')->create([
        'label' => 'Menu Tidak Aman',
    ]);

    Menu::factory()->route('admin.dashboard')->create([
        'label' => 'Dashboard Admin',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertDontSee('href="javascript:alert(1)"', false)
        ->assertDontSee('href="' . route('admin.dashboard') . '"', false);
});

it('displays active child menus with validated public links', function (): void {
    $parent = Menu::factory()->url('/profil')->create([
        'label' => 'Profil',
        'sort_order' => 1,
    ]);

    Menu::factory()
        ->childOf($parent)
        ->url('/profil/sejarah')
        ->create([
            'label' => 'Sejarah',
            'sort_order' => 1,
        ]);

    Menu::factory()
        ->childOf($parent)
        ->url('javascript:alert(1)')
        ->create([
            'label' => 'Submenu Tidak Aman',
            'sort_order' => 2,
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Profil')
        ->assertSee('Sejarah')
        ->assertSee('href="/profil/sejarah"', false)
        ->assertDontSee('href="javascript:alert(1)"', false);
});

it('adds safe rel attributes to external menus opened in a new tab', function (): void {
    $parent = Menu::factory()
        ->url('https://example.org/profil')
        ->create([
            'label' => 'Profil Eksternal',
            'target' => '_blank',
        ]);

    Menu::factory()
        ->childOf($parent)
        ->url('https://example.org/sejarah')
        ->create([
            'label' => 'Sejarah Eksternal',
            'target' => '_blank',
        ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Profil Eksternal')
        ->assertSee('Sejarah Eksternal');

    expect(
        substr_count(
            $response->getContent(),
            'target="_blank" rel="noopener noreferrer"',
        ),
    )->toBe(2);
});

it('rejects external menu URLs containing user credentials', function (): void {
    Menu::factory()
        ->url('https://dinas.go.id@evil.example/profil')
        ->create([
            'label' => 'Tautan Menyesatkan',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(
            'href="https://dinas.go.id@evil.example/profil"',
            false,
        );
});

it('rejects encoded protocol-relative menu destinations', function (): void {
    Menu::factory()
        ->url('/%2F%2Fevil.example')
        ->create([
            'label' => 'Path Terenkode',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(
            'href="/%2F%2Fevil.example"',
            false,
        );
});

it('rejects double-encoded protocol-relative menu destinations', function (): void {
    Menu::factory()
        ->url('/%252F%252Fevil.example')
        ->create([
            'label' => 'URL Encoding Berlapis',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(
            'href="/%252F%252Fevil.example"',
            false,
        );
});

it('allows valid local menu paths with query strings and fragments', function (): void {
    Menu::factory()
        ->url('/profil?bagian=sejarah#arsip')
        ->create([
            'label' => 'Sejarah dan Arsip',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(
            'href="/profil?bagian=sejarah#arsip"',
            false,
        )
        ->assertSee('Sejarah dan Arsip');
});

it('allows valid external HTTP and HTTPS menu URLs', function (): void {
    Menu::factory()
        ->url('http://example.org/profil')
        ->create([
            'label' => 'Profil HTTP',
        ]);

    Menu::factory()
        ->url('https://example.org/layanan')
        ->create([
            'label' => 'Layanan HTTPS',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="http://example.org/profil"', false)
        ->assertSee('href="https://example.org/layanan"', false)
        ->assertSee('Profil HTTP')
        ->assertSee('Layanan HTTPS');
});

it('displays dated published news before published news without a publication date', function (): void {
    $undatedNews = \App\Models\Content::factory()->create([
        'type' => \App\Enums\ContentType::NEWS,
        'status' => \App\Enums\ContentStatus::PUBLISHED,
        'title' => 'Berita Tanpa Tanggal Terbit',
        'published_at' => null,
    ]);

    $datedNews = \App\Models\Content::factory()->create([
        'type' => \App\Enums\ContentType::NEWS,
        'status' => \App\Enums\ContentStatus::PUBLISHED,
        'title' => 'Berita Dengan Tanggal Terbit',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Berita Dengan Tanggal Terbit')
        ->assertSee('Berita Tanpa Tanggal Terbit');

    $content = $response->getContent();

    expect(
        strpos($content, 'Berita Dengan Tanggal Terbit'),
    )->toBeLessThan(
        strpos($content, 'Berita Tanpa Tanggal Terbit'),
    );
});

it('displays only the six most recent published news items', function (): void {
    $newsItems = collect();

    for ($i = 1; $i <= 7; $i++) {
        $newsItems->push(
            \App\Models\Content::factory()->create([
                'type' => \App\Enums\ContentType::NEWS,
                'status' => \App\Enums\ContentStatus::PUBLISHED,
                'title' => "Berita Batas {$i}",
                'published_at' => now()->subDays($i),
            ]),
        );
    }

    $response = $this->get(route('home'));

    $response->assertOk();

    // Berita 1 adalah yang terbaru, sedangkan berita 7 paling lama.
    for ($i = 1; $i <= 6; $i++) {
        $response->assertSee("Berita Batas {$i}");
    }

    $response->assertDontSee('Berita Batas 7');
});

it('does not display active child menus when their parent is inactive or deleted', function (): void {
    $inactiveParent = Menu::factory()->inactive()->create([
        'label' => 'Induk Nonaktif',
    ]);

    Menu::factory()
        ->childOf($inactiveParent)
        ->active()
        ->create([
            'label' => 'Submenu dari Induk Nonaktif',
        ]);

    $deletedParent = Menu::factory()->create([
        'label' => 'Induk Terhapus',
    ]);

    Menu::factory()
        ->childOf($deletedParent)
        ->active()
        ->create([
            'label' => 'Submenu dari Induk Terhapus',
        ]);

    $deletedParent->delete();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Induk Nonaktif')
        ->assertDontSee('Submenu dari Induk Nonaktif')
        ->assertDontSee('Induk Terhapus')
        ->assertDontSee('Submenu dari Induk Terhapus');
});


it('does not render administrative or authenticated local paths as public links', function (): void {
    foreach ([
        '/admin',
        '/admin/dashboard',
        '/admin/menus',
        '/dashboard',
        '/settings/profile',
        '/%61dmin/dashboard',
        '/%2Fadmin/dashboard',
        '/public/../admin/dashboard',
        '/%2e%2e/admin/dashboard',
    ] as $path) {
        Menu::factory()->url($path)->create([
            'label' => 'Tautan Terproteksi ' . $path,
        ]);
    }

    $response = $this->get(route('home'));

    $response->assertOk();

    foreach ([
        '/admin',
        '/admin/dashboard',
        '/admin/menus',
        '/dashboard',
        '/settings/profile',
        '/%61dmin/dashboard',
        '/%2Fadmin/dashboard',
        '/public/../admin/dashboard',
        '/%2e%2e/admin/dashboard',
    ] as $path) {
        $response->assertDontSee('href="' . $path . '"', false);
    }
});
