<?php

declare(strict_types=1);

use Illuminate\Routing\Route;
use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Http\Requests\Admin\Content\UpdateContentRequest;
use App\Models\Content;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('content.update', 'web');
});

function updateContentRequest(
    Content $content,
    array $data,
    ?User $user = null,
): Validator {
    $request = UpdateContentRequest::create(
        "/admin/content/{$content->uuid}",
        'PUT',
        $data,
    );

    $request->setUserResolver(
        fn() => $user,
    );

    $route = new Route(
        ['PUT'],
        '/admin/content/{content}',
        fn() => null,
    );

    $route->bind($request);

    $route->setParameter('content', $content);

    $request->setRouteResolver(
        fn() => $route,
    );

    return ValidatorFacade::make(
        $request->all(),
        $request->rules(),
    );
}

function validUpdateContentData(array $overrides = []): array
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

test('authorized user can submit valid content update data', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'slug' => $content->slug,
        ]),
        $user,
    );

    expect($validator->passes())->toBeTrue();
});

test('user without content update permission is not authorized', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $request = UpdateContentRequest::create(
        "/admin/content/{$content->uuid}",
        'PUT',
        validUpdateContentData(),
    );

    $request->setUserResolver(
        fn() => $user,
    );

    $route = new Route(
        ['PUT'],
        '/admin/content/{content}',
        fn() => null,
    );

    $route->bind($request);

    $route->setParameter('content', $content);

    $request->setRouteResolver(
        fn() => $route,
    );

    expect($request->authorize())->toBeFalse();
});

test('content can keep its existing slug', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create([
        'slug' => 'existing-content',
    ]);

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'slug' => 'existing-content',
        ]),
        $user,
    );

    expect($validator->passes())->toBeTrue();
});

test('content cannot use another content slug', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create([
        'slug' => 'content-one',
    ]);

    Content::factory()->create([
        'slug' => 'content-two',
    ]);

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'slug' => 'content-two',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content slug must use lowercase kebab case', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'slug' => 'Invalid Slug',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content type must be valid enum value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'type' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('content status must be valid enum value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'status' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('unknown author uuid is rejected', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'author_uuid' => fake()->uuid(),
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('existing author uuid is accepted', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'author_uuid' => $author->uuid,
        ]),
        $user,
    );

    expect($validator->passes())->toBeTrue();
});

test('metadata must be an array', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'metadata' => 'invalid',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('published at must be a valid date', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'published_at' => 'not-a-date',
        ]),
        $user,
    );

    expect($validator->fails())->toBeTrue();
});

test('internal identifiers are not part of validated content data', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    $validator = updateContentRequest(
        $content,
        validUpdateContentData([
            'id' => 999,
            'uuid' => fake()->uuid(),
            'author_id' => 999,
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
