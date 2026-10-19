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
