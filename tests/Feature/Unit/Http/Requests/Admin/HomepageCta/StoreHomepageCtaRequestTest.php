<?php

declare(strict_types=1);

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;
use App\Http\Requests\Admin\HomepageCta\StoreHomepageCtaRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

function validateHomepageCta(array $data): Illuminate\Contracts\Validation\Validator
{
    $request = new StoreHomepageCtaRequest;

    return Validator::make($data, $request->rules());
}

function validHomepageCtaData(): array
{
    return [
        'title' => 'Jelajahi Layanan',
        'description' => 'Temukan layanan kami.',
        'sort_order' => 1,
        'is_active' => true,
        'buttons' => [
            [
                'label' => 'Lihat Layanan',
                'type' => 'url',
                'url' => '/layanan',
                'route_name' => null,
                'target' => '_self',
                'sort_order' => 1,
                'is_active' => true,
            ],
        ],
    ];
}

it('accepts valid homepage CTA data', function (): void {
    expect(validateHomepageCta(validHomepageCtaData())->passes())->toBeTrue();
});

it('accepts multiple CTA buttons', function (): void {
    $data = validHomepageCtaData();

    $data['buttons'][] = [
        'label' => 'Baca Berita',
        'type' => 'url',
        'url' => '/berita',
        'route_name' => null,
        'target' => '_blank',
        'sort_order' => 2,
        'is_active' => true,
    ];

    expect(validateHomepageCta($data)->passes())->toBeTrue();
});

it('requires a title', function (): void {
    $data = validHomepageCtaData();
    unset($data['title']);

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('requires at least one CTA button', function (): void {
    $data = validHomepageCtaData();
    $data['buttons'] = [];

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('requires button label', function (): void {
    $data = validHomepageCtaData();
    unset($data['buttons'][0]['label']);

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('rejects unsafe CTA button URLs', function (string $url): void {
    $data = validHomepageCtaData();
    $data['buttons'][0]['url'] = $url;

    expect(validateHomepageCta($data)->fails())->toBeTrue();
})->with([
    'javascript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    '//evil.com',
    'https://user:password@example.com',
    '/admin/users',
    '/dashboard',
    '/settings/profile',
]);

it('requires URL for URL button type', function (): void {
    $data = validHomepageCtaData();
    $data['buttons'][0]['url'] = null;

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('prohibits route name for URL button type', function (): void {
    $data = validHomepageCtaData();
    $data['buttons'][0]['route_name'] = 'home';

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('accepts explicitly allowed named route', function (): void {
    $data = validHomepageCtaData();

    $data['buttons'][0]['type'] = 'route';
    $data['buttons'][0]['url'] = null;
    $data['buttons'][0]['route_name'] = 'home';

    expect(validateHomepageCta($data)->passes())->toBeTrue();
});

it('rejects disallowed named routes', function (string $routeName): void {
    $data = validHomepageCtaData();

    $data['buttons'][0]['type'] = 'route';
    $data['buttons'][0]['url'] = null;
    $data['buttons'][0]['route_name'] = $routeName;

    expect(validateHomepageCta($data)->fails())->toBeTrue();
})->with([
    'admin.dashboard',
    'login',
    'public.news.show',
    'nonexistent.route',
]);

it('prohibits URL for route button type', function (): void {
    $data = validHomepageCtaData();

    $data['buttons'][0]['type'] = 'route';
    $data['buttons'][0]['route_name'] = 'home';

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('rejects invalid button target', function (): void {
    $data = validHomepageCtaData();
    $data['buttons'][0]['target'] = '_parent';

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('rejects negative sort order', function (): void {
    $data = validHomepageCtaData();
    $data['sort_order'] = -1;

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('rejects invalid button status', function (): void {
    $data = validHomepageCtaData();
    $data['buttons'][0]['is_active'] = 'invalid';

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});

it('converts validated data into homepage CTA data object', function (): void {
    $data = validHomepageCtaData();

    $request = new StoreHomepageCtaRequest;
    $request->merge($data);

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $result = $request->toData();

    expect($result)
        ->toBeInstanceOf(HomepageCtaData::class)
        ->and($result->title)->toBe('Jelajahi Layanan')
        ->and($result->description)->toBe('Temukan layanan kami.')
        ->and($result->sortOrder)->toBe(1)
        ->and($result->isActive)->toBeTrue()
        ->and($result->buttons)->toHaveCount(1)
        ->and($result->buttons[0])
        ->toBeInstanceOf(HomepageCtaButtonData::class)
        ->and($result->buttons[0]->label)->toBe('Lihat Layanan')
        ->and($result->buttons[0]->url)->toBe('/layanan');
});

it('rejects button uuid when creating a new homepage CTA', function (): void {
    $data = validHomepageCtaData();

    $data['buttons'][0]['uuid'] = (string) Str::uuid();

    expect(validateHomepageCta($data)->fails())->toBeTrue();
});
