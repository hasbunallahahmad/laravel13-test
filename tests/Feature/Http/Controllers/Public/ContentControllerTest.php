<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;

test('public news page displays published news by slug', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Publik',
        'slug' => 'berita-publik',
        'body' => '<p>Isi berita publik.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response
        ->assertOk()
        ->assertSee('Berita Publik')
        ->assertSee('Isi berita publik.');
});

test('public news page returns not found for news scheduled for future publication', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Terjadwal',
        'slug' => 'berita-terjadwal',
        'body' => '<p>Belum boleh tampil.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response->assertNotFound();
});

test('public news page returns not found for draft news', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Draft',
        'slug' => 'berita-draft',
        'body' => '<p>Berita draft.</p>',
        'status' => ContentStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response->assertNotFound();
});

test('public news page returns not found for archived news', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Diarsipkan',
        'slug' => 'berita-diarsipkan',
        'body' => '<p>Berita yang sudah diarsipkan.</p>',
        'status' => ContentStatus::ARCHIVED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response->assertNotFound();
});

test('public news page returns not found for soft deleted news', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Terhapus',
        'slug' => 'berita-terhapus',
        'body' => '<p>Berita yang sudah dihapus.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $content->delete();

    $response = $this->get('/berita/' . $content->slug);

    $response->assertNotFound();
});

test('public news page returns not found when slug belongs to another content type', function () {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
        'title' => 'Halaman Profil',
        'slug' => 'profil',
        'body' => '<p>Halaman profil.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response->assertNotFound();
});

test('public news page returns not found for unknown slug', function () {
    $response = $this->get('/berita/slug-yang-tidak-ada');

    $response->assertNotFound();
});

test('public announcement page displays published announcement by slug', function () {
    $content = Content::factory()->create([
        'type' => ContentType::ANNOUNCEMENT,
        'title' => 'Pengumuman Publik',
        'slug' => 'pengumuman-publik',
        'body' => '<p>Isi pengumuman publik.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/pengumuman/' . $content->slug);

    $response
        ->assertOk()
        ->assertSee('Pengumuman Publik')
        ->assertSee('Isi pengumuman publik.');
});

test('public announcement page returns not found for draft announcement', function () {
    $content = Content::factory()->create([
        'type' => ContentType::ANNOUNCEMENT,
        'title' => 'Pengumuman Draft',
        'slug' => 'pengumuman-draft',
        'body' => '<p>Pengumuman draft.</p>',
        'status' => ContentStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get('/pengumuman/' . $content->slug);

    $response->assertNotFound();
});

test('public agenda page displays published event by slug', function () {
    $content = Content::factory()->create([
        'type' => ContentType::EVENT,
        'title' => 'Agenda Kegiatan',
        'slug' => 'agenda-kegiatan',
        'body' => '<p>Informasi agenda kegiatan.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/agenda/' . $content->slug);

    $response
        ->assertOk()
        ->assertSee('Agenda Kegiatan')
        ->assertSee('Informasi agenda kegiatan.');
});

test('public content sanitizes stored HTML before rendering it', function () {
    $maliciousBody = '<script>alert(\'xss-script\')</script>'
        . '<p>Konten aman.</p>'
        . '<img src="x" onerror="alert(\'xss-image\')">'
        . '<a href="javascript:alert(\'xss-link\')">tautan</a>';

    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Uji Sanitasi',
        'slug' => 'berita-uji-sanitasi',
        'body' => $maliciousBody,
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $sanitizedBody = app(
        \App\Services\Content\ContentHtmlSanitizer::class,
    )->sanitize($maliciousBody);

    expect($sanitizedBody)
        ->toContain('<p>Konten aman.</p>')
        ->not->toContain('<script')
        ->not->toContain('onerror')
        ->not->toContain('javascript:');

    $response = $this->get('/berita/' . $content->slug);

    $response
        ->assertOk()
        ->assertSee('<p>Konten aman.</p>', false)
        ->assertSee('Berita Uji Sanitasi')
        ->assertDontSee('onerror', false)
        ->assertDontSee('javascript:', false);

    expect($content->fresh()->body)->toBe($maliciousBody);
});

test('public article page displays published article by slug', function () {
    $content = Content::factory()->create([
        'type' => ContentType::ARTICLE,
        'title' => 'Artikel Literasi',
        'slug' => 'artikel-literasi',
        'excerpt' => 'Pentingnya budaya literasi.',
        'body' => '<p>Isi artikel literasi.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/artikel/' . $content->slug);

    $response
        ->assertOk()
        ->assertSee('Artikel Literasi')
        ->assertSee('Pentingnya budaya literasi.');
});


test('public content page uses the shared public site shell', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Layout Bersama',
        'slug' => 'berita-layout-bersama',
        'body' => '<p>Isi berita untuk pengujian layout.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $this->get('/berita/' . $content->slug)
        ->assertOk()
        ->assertSee('<header', false)
        ->assertSee('Pemerintah Kota Semarang')
        ->assertSee('<footer', false)
        ->assertSee('Dinas Arsip dan Perpustakaan Kota Semarang');
});


test('public content page renders canonical and Open Graph metadata', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita SEO Publik',
        'slug' => 'berita-seo-publik',
        'excerpt' => 'Deskripsi SEO untuk berita publik.',
        'body' => '<p>Isi berita SEO.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $canonicalUrl = url('/berita/' . $content->slug);

    $this->get('/berita/' . $content->slug)
        ->assertOk()
        ->assertSee(
            '<link rel="canonical" href="' . $canonicalUrl . '">',
            false,
        )
        ->assertSee(
            '<meta property="og:title" content="Berita SEO Publik">',
            false,
        )
        ->assertSee(
            '<meta property="og:description" content="Deskripsi SEO untuk berita publik.">',
            false,
        )
        ->assertSee(
            '<meta property="og:url" content="' . $canonicalUrl . '">',
            false,
        );
});


test('public SEO metadata escapes content that could inject HTML attributes', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Metadata Aman',
        'slug' => 'berita-metadata-aman',
        'excerpt' => 'Deskripsi" onmouseover="alert(1)',
        'body' => '<p>Isi berita.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $response = $this->get('/berita/' . $content->slug);

    $response
        ->assertOk()
        ->assertDontSee('onmouseover="alert(1)"', false);
});


test('public content page uses a safe fallback description when excerpt is empty', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'title' => 'Berita Tanpa Ringkasan',
        'slug' => 'berita-tanpa-ringkasan',
        'excerpt' => null,
        'body' => '<p>Isi berita tanpa ringkasan.</p>',
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    $this->get('/berita/' . $content->slug)
        ->assertOk()
        ->assertSee(
            '<meta name="description" content="Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.">',
            false,
        )
        ->assertSee(
            '<meta property="og:description" content="Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.">',
            false,
        );
});
