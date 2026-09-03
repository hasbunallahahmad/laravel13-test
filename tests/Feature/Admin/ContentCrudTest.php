<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\User;

test('guest cannot access content create page', function () {
    $response = $this->get(route('admin.contents.create'));

    $response->assertRedirect(route('login'));
});

test('user without create permission cannot access content create page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.create'));

    $response->assertForbidden();
});

test('authorized user can access content create page', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.create'));

    $response->assertOk();
    $response->assertSee('Create Content');
});

test('authorized user can create content', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'excerpt' => 'Test excerpt',
            'body' => 'Test article body.',
            'status' => ContentStatus::DRAFT->value,
            'published_at' => null,
            'author_uuid' => $author->uuid,
            'metadata' => [
                'featured' => false,
            ],
        ]);

    $content = Content::query()
        ->where('slug', 'test-article')
        ->first();

    expect($content)->not->toBeNull();
    expect($content->author_id)->toBe($author->id);

    $response
        ->assertRedirect(route('admin.contents.edit', $content))
        ->assertSessionHas('success', 'Content berhasil dibuat.');
});

test('content creation rejects duplicate slug', function () {
    $user = User::factory()->create();
    $existing = Content::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Another Article',
            'slug' => $existing->slug,
            'body' => 'Test body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionHasErrors('slug');
});

test('authorized user can update content', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $content = Content::factory()->create([
        'title' => 'Old Title',
        'slug' => 'old-title',
    ]);

    $user->givePermissionTo('content.update');

    $response = $this
        ->actingAs($user)
        ->put(route('admin.contents.update', $content), [
            'type' => ContentType::NEWS->value,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'excerpt' => 'Updated excerpt',
            'body' => 'Updated body.',
            'status' => ContentStatus::PUBLISHED->value,
            'published_at' => now()->toDateTimeString(),
            'author_uuid' => $author->uuid,
            'metadata' => [
                'featured' => true,
            ],
        ]);

    $content->refresh();

    expect($content->title)->toBe('Updated Title');
    expect($content->slug)->toBe('updated-title');
    expect($content->type)->toBe(ContentType::NEWS);
    expect($content->status)->toBe(ContentStatus::PUBLISHED);
    expect($content->author_id)->toBe($author->id);

    $response
        ->assertRedirect(route('admin.contents.edit', $content))
        ->assertSessionHas('success', 'Content berhasil diperbarui.');
});

test('content update allows its existing slug', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create([
        'slug' => 'existing-slug',
    ]);

    $user->givePermissionTo('content.update');

    $response = $this
        ->actingAs($user)
        ->put(route('admin.contents.update', $content), [
            'type' => $content->type->value,
            'title' => 'Updated Title',
            'slug' => 'existing-slug',
            'body' => 'Updated body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionDoesntHaveErrors();
});

test('user without update permission cannot update content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create([
        'title' => 'Original Title',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('admin.contents.update', $content), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Changed Title',
            'slug' => $content->slug,
            'body' => 'Changed body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertForbidden();

    expect($content->refresh()->title)->toBe('Original Title');
});

test('authorized user can delete content', function () {
    $user = User::factory()->create();
    $content = Content::factory()->create();

    $user->givePermissionTo('content.delete');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response
        ->assertRedirect(route('admin.contents.index'))
        ->assertSessionHas('success', 'Content berhasil dihapus.');

    expect(Content::query()->find($content->id))->toBeNull();
});

test('user without delete permission cannot delete content', function () {
    $user = User::factory()->create();
    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response->assertForbidden();

    expect(Content::query()->find($content->id))->not->toBeNull();
});

test('content routes expose uuid instead of internal numeric id', function () {
    $user = User::factory()->create();
    $content = Content::factory()->create();

    $user->givePermissionTo('content.update');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.edit', $content));

    $response->assertOk();

    expect((string) $content->getRouteKey())
        ->not->toBe((string) $content->id);

    expect($content->getRouteKey())
        ->toBe($content->uuid);
});

test('guest cannot access content index', function () {
    $response = $this->get(route('admin.contents.index'));

    $response->assertRedirect(route('login'));
});

test('user without view permission cannot access content index', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertForbidden();
});

test('authorized user can access content index', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    Content::factory()->create([
        'title' => 'Test Content',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();
    $response->assertSee('Test Content');
});

test('content index displays paginated contents', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    Content::factory()->count(16)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    expect($response->viewData('contents')->perPage())
        ->toBe(15);

    expect($response->viewData('contents')->total())
        ->toBe(16);
});

test('content index eager loads authors', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $author = User::factory()->create([
        'name' => 'Content Author',
    ]);

    Content::factory()->create([
        'title' => 'Content With Author',
        'author_id' => $author->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $contents = $response->viewData('contents');

    expect($contents->first()->relationLoaded('author'))
        ->toBeTrue();
});

test('content creation rejects invalid slug format', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Invalid Slug Content',
            'slug' => 'Invalid Slug!',
            'body' => 'Test body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionHasErrors('slug');
});

test('content creation rejects invalid type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => 'invalid-type',
            'title' => 'Test Content',
            'slug' => 'test-content',
            'body' => 'Test body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionHasErrors('type');
});

test('content creation rejects invalid status', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Test Content',
            'slug' => 'test-content',
            'body' => 'Test body.',
            'status' => 'invalid-status',
            'author_uuid' => null,
        ]);

    $response->assertSessionHasErrors('status');
});

test('content creation rejects unknown author uuid', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.contents.store'), [
            'type' => ContentType::ARTICLE->value,
            'title' => 'Test Content',
            'slug' => 'test-content',
            'body' => 'Test body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => fake()->uuid(),
        ]);

    $response->assertSessionHasErrors('author_uuid');
});

test('content update rejects slug belonging to another content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create([
        'slug' => 'original-content',
    ]);

    $otherContent = Content::factory()->create([
        'slug' => 'another-content',
    ]);

    $user->givePermissionTo('content.update');

    $response = $this
        ->actingAs($user)
        ->put(route('admin.contents.update', $content), [
            'type' => $content->type->value,
            'title' => 'Updated Content',
            'slug' => $otherContent->slug,
            'body' => 'Updated body.',
            'status' => ContentStatus::DRAFT->value,
            'author_uuid' => null,
        ]);

    $response->assertSessionHasErrors('slug');
});

test('content route binding does not expose numeric id', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $user->givePermissionTo('content.update');

    $response = $this
        ->actingAs($user)
        ->get('/admin/contents/' . $content->id . '/edit');

    $response->assertNotFound();
});

test('deleted content is soft deleted', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $user->givePermissionTo('content.delete');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response->assertRedirect(route('admin.contents.index'));

    expect(Content::query()->find($content->id))
        ->toBeNull();

    expect(Content::withTrashed()->find($content->id))
        ->not->toBeNull();

    expect(Content::withTrashed()->find($content->id)->deleted_at)
        ->not->toBeNull();
});

test('soft deleted content does not appear in content index', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $activeContent = Content::factory()->create([
        'title' => 'Active Content',
    ]);

    $deletedContent = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $deletedContent->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();
    $response->assertSee('Active Content');
    $response->assertDontSee('Deleted Content');
});

test('authorized user can view content trash', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    Content::factory()->create([
        'title' => 'Active Content',
    ]);

    $deleted = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $deleted->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.trash'));

    $response->assertOk();
    $response->assertSee('Deleted Content');
    $response->assertDontSee('Active Content');
});

test('user without view permission cannot view content trash', function () {
    $user = User::factory()->create();

    $deleted = Content::factory()->create();
    $deleted->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.trash'));

    $response->assertForbidden();
});

test('authorized user can restore deleted content', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.restore');

    $content = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $content->delete();

    expect(Content::query()->find($content->id))
        ->toBeNull();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.contents.restore', $content->uuid));

    $response
        ->assertRedirect(route('admin.contents.trash'))
        ->assertSessionHas('success', 'Content berhasil dipulihkan.');

    expect(Content::query()->find($content->id))
        ->not->toBeNull();

    expect(Content::withTrashed()->find($content->id)->deleted_at)
        ->toBeNull();
});

test('user without restore permission cannot restore deleted content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();
    $content->delete();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.contents.restore', $content->uuid));

    $response->assertForbidden();

    expect(Content::withTrashed()->find($content->id)->deleted_at)
        ->not->toBeNull();
});

test('only authorized user can permanently delete content', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.force-delete');

    $content = Content::factory()->create();

    $content->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.force-destroy', $content->uuid));

    $response
        ->assertRedirect(route('admin.contents.trash'))
        ->assertSessionHas('success', 'Content berhasil dihapus permanen.');

    expect(Content::withTrashed()->find($content->id))
        ->toBeNull();
});

test('user without force delete permission cannot permanently delete content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $content->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.force-destroy', $content->uuid));

    $response->assertForbidden();

    expect(Content::withTrashed()->find($content->id))
        ->not->toBeNull();
});

test('restore route does not accept numeric content id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.restore');

    $content = Content::factory()->create();
    $content->delete();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.contents.restore', $content->id));

    $response->assertNotFound();

    expect(Content::withTrashed()->find($content->id)->deleted_at)
        ->not->toBeNull();
});

test('force delete route does not accept numeric content id', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.force-delete');

    $content = Content::factory()->create();
    $content->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.force-destroy', $content->id));

    $response->assertNotFound();

    expect(Content::withTrashed()->find($content->id))
        ->not->toBeNull();
});

test('content trash hides restore action without restore permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $deleted = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $deleted->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.trash'));

    $response->assertOk();
    $response->assertSee('Deleted Content');

    $response->assertDontSee(
        route('admin.contents.restore', $deleted),
        false,
    );
});

test('content trash shows restore action with restore permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.restore',
    ]);

    $deleted = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $deleted->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.trash'));

    $response->assertOk();
    $response->assertSee('Deleted Content');

    $response->assertSee(
        route('admin.contents.restore', $deleted),
        false,
    );
});

test('content trash shows permanent delete action with force delete permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.force-delete',
    ]);

    $deleted = Content::factory()->create([
        'title' => 'Deleted Content',
    ]);

    $deleted->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.trash'));

    $response->assertOk();
    $response->assertSee('Deleted Content');

    $response->assertSee(
        route('admin.contents.force-destroy', $deleted),
        false,
    );
});
