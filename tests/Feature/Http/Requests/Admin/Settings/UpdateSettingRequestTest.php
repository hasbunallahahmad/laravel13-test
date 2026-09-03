<?php

declare(strict_types=1);

use App\Data\Settings\SettingData;
use App\Http\Requests\Admin\Settings\UpdateSettingRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;

function makeUpdateSettingRequest(
    array $data = [],
    string $group = 'general',
    string $key = 'site_name',
): UpdateSettingRequest {
    $request = UpdateSettingRequest::create(
        "/admin/settings/{$group}/{$key}",
        'PUT',
        $data,
    );

    $route = new Route(
        ['PUT'],
        '/admin/settings/{group}/{key}',
        fn() => null,
    );

    $route->bind($request);

    $route->setParameter('group', $group);
    $route->setParameter('key', $key);

    $request->setRouteResolver(
        fn() => $route,
    );

    $request->setContainer(app());

    return $request;
}

function makeUpdateSettingValidator(
    array $data,
    string $group = 'general',
    string $key = 'site_name',
) {
    $request = makeUpdateSettingRequest(
        $data,
        $group,
        $key,
    );

    return Validator::make(
        $request->all(),
        $request->rules(),
    );
}

test('it validates a valid string setting payload', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it validates a valid integer setting payload', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 20,
        'type' => 'integer',
        'is_public' => false,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid integer setting value', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'invalid-number',
        'type' => 'integer',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it validates a valid boolean setting value', function () {
    $validator = makeUpdateSettingValidator([
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid boolean setting value', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'invalid-boolean',
        'type' => 'boolean',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it validates a valid json setting value', function () {
    $validator = makeUpdateSettingValidator([
        'value' => '{"theme":"dark"}',
        'type' => 'json',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid json setting value', function () {
    $validator = makeUpdateSettingValidator([
        'value' => '{"theme":"dark"',
        'type' => 'json',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it requires a supported setting type when creating a new setting', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'Website Arpus',
        'type' => 'unsupported',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('type'))->toBeTrue();
});

test('it requires a setting type when creating a new setting', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'Website Arpus',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('type'))->toBeTrue();
});

test('it allows a null value when nullable', function () {
    $validator = makeUpdateSettingValidator([
        'value' => null,
        'type' => 'string',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it validates is_public as a boolean', function () {
    $validator = makeUpdateSettingValidator([
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => 'invalid',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('is_public'))->toBeTrue();
});

test('it can transform validated request data into setting data', function () {
    $request = makeUpdateSettingRequest([
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data)
        ->toBeInstanceOf(SettingData::class)
        ->and($data->group)->toBe('general')
        ->and($data->key)->toBe('site_name')
        ->and($data->value)->toBe('Website Arpus')
        ->and($data->type)->toBe('string')
        ->and($data->isPublic)->toBeTrue();
});

test('it uses group and key from trusted route parameters', function () {
    $request = makeUpdateSettingRequest(
        [
            'value' => 'Website Arpus Kota Semarang',
            'type' => 'string',
            'is_public' => true,

            /*
             * These values must not override trusted route parameters.
             */
            'group' => 'malicious-group',
            'key' => 'malicious-key',
        ],
        'general',
        'site_name',
    );

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->group)->toBe('general')
        ->and($data->key)->toBe('site_name');
});

test('it defaults is_public to false when not provided for a new setting', function () {
    $request = makeUpdateSettingRequest([
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->isPublic)->toBeFalse();
});

test('it preserves boolean values when transforming to setting data', function () {
    $request = makeUpdateSettingRequest([
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ], 'system', 'maintenance_mode');

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->group)->toBe('system')
        ->and($data->key)->toBe('maintenance_mode')
        ->and($data->value)->toBeTrue()
        ->and($data->type)->toBe('boolean')
        ->and($data->isPublic)->toBeFalse();
});

test('it transforms valid json strings into arrays in setting data', function () {
    $json = '{"theme":"dark","layout":"modern"}';

    $request = makeUpdateSettingRequest([
        'value' => $json,
        'type' => 'json',
        'is_public' => true,
    ], 'general', 'theme_config');

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->group)->toBe('general')
        ->and($data->key)->toBe('theme_config')
        ->and($data->value)->toBe([
            'theme' => 'dark',
            'layout' => 'modern',
        ])
        ->and($data->type)->toBe('json')
        ->and($data->isPublic)->toBeTrue();
});

test('it preserves null values when transforming to setting data', function () {
    $request = makeUpdateSettingRequest([
        'value' => null,
        'type' => 'string',
    ], 'general', 'optional_value');

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->group)->toBe('general')
        ->and($data->key)->toBe('optional_value')
        ->and($data->value)->toBeNull()
        ->and($data->type)->toBe('string')
        ->and($data->isPublic)->toBeFalse();
});
