<?php

declare(strict_types=1);

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;
use App\Http\Requests\Admin\HomepageCta\UpdateHomepageCtaRequest;
use App\Models\HomepageCta;
use Illuminate\Support\Facades\Validator;

function validateHomepageCtaUpdate(
    HomepageCta $homepageCta,
    array $data,
): Illuminate\Contracts\Validation\Validator {
    $request = UpdateHomepageCtaRequest::create(
        '/admin/homepage-ctas/'.$homepageCta->uuid,
        'PATCH',
        $data,
    );

    $request->setRouteResolver(
        fn () => new class($homepageCta)
        {
            public function __construct(
                private readonly HomepageCta $homepageCta,
            ) {}

            public function parameter(string $key): mixed
            {
                return $key === 'homepageCta'
                    ? $this->homepageCta
                    : null;
            }
        },
    );

    return Validator::make($data, $request->rules());
}

it('accepts valid homepage CTA data when updating', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();
    $data['title'] = 'CTA Baru';

    expect(validateHomepageCtaUpdate($homepageCta, $data)->passes())->toBeTrue();
});

it('rejects unsafe button URL when updating', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['url'] = 'javascript:alert(1)';

    expect(validateHomepageCtaUpdate($homepageCta, $data)->fails())->toBeTrue();
});

it('rejects disallowed named route when updating', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['type'] = 'route';
    $data['buttons'][0]['url'] = null;
    $data['buttons'][0]['route_name'] = 'admin.dashboard';

    expect(validateHomepageCtaUpdate($homepageCta, $data)->fails())->toBeTrue();
});

it('rejects invalid target when updating', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['target'] = '_parent';

    expect(validateHomepageCtaUpdate($homepageCta, $data)->fails())->toBeTrue();
});
function validHomepageCtaUpdateData(): array
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

it('converts validated data into homepage CTA data object when updating', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();

    $request = UpdateHomepageCtaRequest::create(
        '/admin/homepage-ctas/'.$homepageCta->uuid,
        'PATCH',
        $data,
    );

    $request->setRouteResolver(
        fn () => new class($homepageCta)
        {
            public function __construct(
                private readonly HomepageCta $homepageCta,
            ) {}

            public function parameter(string $key): mixed
            {
                return $key === 'homepageCta'
                    ? $this->homepageCta
                    : null;
            }
        },
    );

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
        ->and($result->buttons)->toHaveCount(1)
        ->and($result->buttons[0])
        ->toBeInstanceOf(HomepageCtaButtonData::class);
});

it('accepts an existing button uuid belonging to the homepage CTA', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $button = $homepageCta->buttons()->create([
        'label' => 'Layanan Lama',
        'type' => 'url',
        'url' => '/layanan-lama',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['uuid'] = $button->uuid;

    expect(validateHomepageCtaUpdate($homepageCta, $data)->passes())
        ->toBeTrue();
});

it('rejects a button uuid belonging to another homepage CTA', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Pertama',
    ]);

    $anotherHomepageCta = HomepageCta::query()->create([
        'title' => 'CTA Kedua',
    ]);

    $button = $anotherHomepageCta->buttons()->create([
        'label' => 'Tombol CTA Kedua',
        'type' => 'url',
        'url' => '/layanan-lain',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['uuid'] = $button->uuid;

    expect(validateHomepageCtaUpdate($homepageCta, $data)->fails())
        ->toBeTrue();
});

it('rejects an unknown button uuid when updating homepage CTA', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Lama',
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['uuid'] = '019a1234-5678-7abc-8def-0123456789ab';

    expect(validateHomepageCtaUpdate($homepageCta, $data)->fails())
        ->toBeTrue();
});

it('preserves existing button uuid when converting update data into DTO', function (): void {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'description' => 'Temukan layanan kami.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $existingButton = $homepageCta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $data = validHomepageCtaUpdateData();
    $data['buttons'][0]['uuid'] = $existingButton->uuid;

    $request = UpdateHomepageCtaRequest::create(
        '/admin/homepage-ctas/'.$homepageCta->uuid,
        'PATCH',
        $data,
    );

    $request->setRouteResolver(
        fn () => new class($homepageCta)
        {
            public function __construct(
                private readonly HomepageCta $homepageCta,
            ) {}

            public function parameter(string $key): mixed
            {
                return $key === 'homepageCta'
                    ? $this->homepageCta
                    : null;
            }
        },
    );

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $dto = $request->toData();

    expect($dto)->toBeInstanceOf(HomepageCtaData::class)
        ->and($dto->buttons[0])->toBeInstanceOf(HomepageCtaButtonData::class)
        ->and($dto->buttons[0]->uuid)->toBe($existingButton->uuid);
});
