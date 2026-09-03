<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Http\Requests\Admin\Content\StoreContentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('content.create', 'web');
});

function storeContentRequest(array $data, ?User $user = null): \Illuminate\Contracts\Validation\Validator
{
    $request = StoreContentRequest::create(
        '/admin/content',
        'POST',
        $data,
    );

    $request->setUserResolver(
        fn() => $user,
    );

    return Validator::make(
        $request->all(),
        $request->rules(),
    );
}

function validStoreContentData(array $overrides = []): array
{
    return array_merge([
        'type' => ContentType::ARTICLE->value,
        'title' => 'Berita Terbaru',
        'slug' => 'berita-terbaru',
        'excerpt' => 'Ringkasan berita terbaru.',
        'body' => 'Isi berita terbaru.',
        'status' => ContentStatus::DRAFT->value,
        'published_at' => null,
        'author_uuid' => null,
        'metadata' => [
            'featured' => false,
        ],
    ], $overrides);
}

test('authorized user can submit valid content data', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.create');

    $validator = storeContentRequest(
        validStoreContentData(),
        $user,
    );

    expect($validator->passes())->toBeTrue();
});

test('user without content create permission is not authorized', function () {
    $user = User::factory()->create();

    $request = StoreContentRequest::create(
        '/admin/content',
        'POST',
        validStoreContentData(),
    );

    $request->setUserResolver(
        fn() => $user,
    );

    expect($request->authorize())->toBeFalse();
});

test('content type must be valid enum value', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'type' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content title is required', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'title' => null,
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content title cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'title' => str_repeat('a', 256),
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content slug must use lowercase kebab case', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'slug' => 'Berita Terbaru',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content slug must be unique', function () {
    $user = User::factory()->create();

    \App\Models\Content::factory()->create([
        'slug' => 'berita-terbaru',
    ]);

    $validator = storeContentRequest(
        validStoreContentData(),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content body is required', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'body' => null,
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content status must be valid enum value', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'status' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('published at must be a valid date', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'published_at' => 'not-a-date',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('author uuid must reference an existing user', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'author_uuid' => $author->uuid,
        ]),
        $user,
    );

    expect($validator->passes())->toBeTrue();
});

test('unknown author uuid is rejected', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'author_uuid' => fake()->uuid(),
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('metadata must be an array', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'metadata' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('internal identifiers are not accepted as content input', function () {
    $user = User::factory()->create();

    $validator = storeContentRequest(
        validStoreContentData([
            'id' => 123,
            'uuid' => fake()->uuid(),
            'author_id' => 123,
        ]),
        $user,
    );

    expect($validator->passes())->toBeTrue();

    $validated = $validator->validated();

    expect($validated)
        ->not->toHaveKey('id')
        ->not->toHaveKey('uuid')
        ->not->toHaveKey('author_id');
});
