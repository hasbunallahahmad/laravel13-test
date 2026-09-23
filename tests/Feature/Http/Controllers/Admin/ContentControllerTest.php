<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('content.view');
    Permission::findOrCreate('content.create');
    Permission::findOrCreate('content.update');
    Permission::findOrCreate('content.delete');
});

test('guest cannot access content index', function () {
    $response = $this->get(route('admin.contents.index'));

    $response->assertRedirect(route('login'));
});

test('user with view permission can access content index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();
    $response->assertViewIs('admin.contents.index');
});

test('user with create permission can access create page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.create'));

    $response->assertOk();
    $response->assertViewIs('admin.contents.create');
});

test('user with update permission can access edit page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.edit', $content));

    $response->assertOk();
    $response->assertViewIs('admin.contents.edit');
});

test('edit route resolves content by uuid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("/admin/contents/{$content->uuid}/edit");

    $response->assertOk();
    $response->assertViewHas('content', function (Content $resolved) use ($content) {
        return $resolved->is($content);
    });
});

test('edit route does not resolve content by numeric id', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("/admin/contents/{$content->id}/edit");

    $response->assertNotFound();
});

test('user without update permission cannot access edit page', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.edit', $content));

    $response->assertForbidden();
});

test('user with delete permission can delete content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.delete');

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response
        ->assertRedirect(route('admin.contents.index'))
        ->assertSessionHas('success');

    expect(
        Content::withTrashed()->find($content->id)->trashed()
    )->toBeTrue();
});

test('user without delete permission cannot delete content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response->assertForbidden();

    expect($content->fresh()->trashed())->toBeFalse();
});

test('authorized user can create content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.create');

    $author = User::factory()->create();

    $payload = [
        'type' => ContentType::ARTICLE->value,
        'title' => 'Artikel Baru',
        'slug' => 'artikel-baru',
        'excerpt' => 'Ringkasan artikel.',
        'body' => 'Isi artikel baru.',
        'status' => ContentStatus::DRAFT->value,
        'published_at' => null,
        'author_uuid' => $author->uuid,
        'metadata' => [
            'source' => 'admin',
        ],
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), $payload);

    $response->assertSessionHas('success');

    $content = Content::query()
        ->where('slug', 'artikel-baru')
        ->first();

    expect($content)->not->toBeNull()
        ->and($content->author_id)->toBe($author->id);
});

test('authorized user can update content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $author = User::factory()->create();

    $content = Content::factory()->create([
        'title' => 'Judul Lama',
        'slug' => 'judul-lama',
    ]);

    $body = <<<'HTML'
<p>Body baru dengan gambar.</p>
<p><img src="http://localhost:8000/storage/media/2026/09/example.webp" alt="Garuda Pancasila" width="289" height="300"></p>
HTML;

    $payload = [
        'type' => ContentType::NEWS->value,
        'title' => 'Judul Baru',
        'slug' => 'judul-baru',
        'excerpt' => 'Excerpt baru.',
        'body' => $body,
        'status' => ContentStatus::REVIEW->value,
        'published_at' => null,
        'author_uuid' => $author->uuid,
        'metadata' => [
            'updated_by_test' => true,
        ],
    ];

    $response = $this
        ->actingAs($user)
        ->put(route('admin.contents.update', $content), $payload);

    $response
        ->assertRedirect(route('admin.contents.index'))
        ->assertSessionHas('success', 'Content berhasil diperbarui.');

    $content->refresh();

    expect($content->title)->toBe('Judul Baru')
        ->and($content->slug)->toBe('judul-baru')
        ->and($content->type)->toBe(ContentType::NEWS)
        ->and($content->status)->toBe(ContentStatus::REVIEW)
        ->and($content->author_id)->toBe($author->id)
        ->and($content->body)->toContain('<img')
        ->and($content->body)->toContain('alt="Garuda Pancasila"')
        ->and($content->body)->toContain('width="289"')
        ->and($content->body)->toContain('height="300"');
});

test('content internal identifiers cannot be mass assigned through controller', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.create');

    $author = User::factory()->create();

    $payload = [
        'id' => 999999,
        'uuid' => '00000000-0000-0000-0000-000000000001',
        'type' => ContentType::ARTICLE->value,
        'title' => 'Secure Content',
        'slug' => 'secure-content',
        'excerpt' => null,
        'body' => 'Secure body.',
        'status' => ContentStatus::DRAFT->value,
        'published_at' => null,
        'author_uuid' => $author->uuid,
        'author_id' => 999999,
        'metadata' => null,
        'deleted_at' => now(),
    ];

    $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), $payload)
        ->assertSessionHas('success');

    $content = Content::query()
        ->where('slug', 'secure-content')
        ->firstOrFail();

    expect($content->id)->not->toBe(999999)
        ->and($content->uuid)->not->toBe($payload['uuid'])
        ->and($content->author_id)->toBe($author->id)
        ->and($content->deleted_at)->toBeNull();
});

test('content creation preserves image html in body', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $body = <<<'HTML'
<p>Konten dengan gambar.</p>
<p><img src="http://localhost:8000/storage/media/2026/09/example.webp" alt="Garuda Pancasila" width="289" height="300"></p>
HTML;

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Content With Image',
            'slug' => 'content-with-image',
            'body' => $body,
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionDoesntHaveErrors();

    $content = Content::query()
        ->where('slug', 'content-with-image')
        ->first();

    expect($content)->not->toBeNull();
    expect($content->body)->toContain('<img');
    expect($content->body)->toContain('alt="Garuda Pancasila"');
    expect($content->body)->toContain('width="289"');
    expect($content->body)->toContain('height="300"');
});

test('edit page preserves image html from saved content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $body = <<<'HTML'
<p>Konten dengan gambar.</p>
<p><img src="http://localhost:8000/storage/media/2026/09/example.webp" alt="Garuda Pancasila" width="289" height="300"></p>
HTML;

    $content = Content::factory()->create([
        'title' => 'Content With Image',
        'slug' => 'content-with-image',
        'body' => $body,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.edit', $content));

    $response
        ->assertOk()
        ->assertViewIs('admin.contents.edit')
        ->assertSee('Garuda Pancasila', false)
        ->assertSee('example.webp', false)
        ->assertSee('width=&quot;289&quot;', false)
        ->assertSee('height=&quot;300&quot;', false);
});
