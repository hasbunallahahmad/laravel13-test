<?php

use App\Data\Settings\SettingData;
use App\Http\Requests\Admin\Settings\UpdateSettingRequest;
use Illuminate\Support\Facades\Validator;

function makeUpdateSettingValidator(array $data)
{
    $request = new UpdateSettingRequest();

    $request->merge($data);

    return Validator::make(
        $data,
        $request->rules(),
    );
}

test('it validates a valid string setting payload', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it requires a setting group', function () {
    $validator = makeUpdateSettingValidator([
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('group'))->toBeTrue();
});

test('it rejects an empty setting group', function () {
    $validator = makeUpdateSettingValidator([
        'group' => '',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('group'))->toBeTrue();
});

test('it requires a setting key', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('key'))->toBeTrue();
});

test('it rejects an empty setting key', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => '',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('key'))->toBeTrue();
});

test('it requires a supported setting type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'unsupported',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('type'))->toBeTrue();
});

test('it validates integer values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'items_per_page',
        'value' => 20,
        'type' => 'integer',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects invalid integer values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'items_per_page',
        'value' => 'invalid-number',
        'type' => 'integer',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it validates boolean values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects invalid boolean values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'maintenance_mode',
        'value' => 'invalid-boolean',
        'type' => 'boolean',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it validates valid json values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'theme_config',
        'value' => '{"theme":"dark"}',
        'type' => 'json',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it rejects invalid json values according to their type', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'theme_config',
        'value' => '{"theme":"dark"',
        'type' => 'json',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('value'))->toBeTrue();
});

test('it allows a null value when nullable', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'optional_value',
        'value' => null,
        'type' => 'string',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('it validates is_public as a boolean', function () {
    $validator = makeUpdateSettingValidator([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => 'invalid',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('is_public'))->toBeTrue();
});

test('it can transform validated request data into setting data', function () {
    $request = new UpdateSettingRequest();

    $request->merge([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    $request->setContainer(app());

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

test('it defaults is_public to false when not provided in setting data', function () {
    $request = new UpdateSettingRequest();

    $request->merge([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    $request->setContainer(app());

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
    $request = new UpdateSettingRequest();

    $request->merge([
        'group' => 'general',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    $request->setContainer(app());

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->value)->toBeTrue()
        ->and($data->type)->toBe('boolean');
});

test('it preserves json values when transforming to setting data', function () {
    $json = '{"theme":"dark","layout":"modern"}';

    $request = new UpdateSettingRequest();

    $request->merge([
        'group' => 'general',
        'key' => 'theme_config',
        'value' => $json,
        'type' => 'json',
        'is_public' => true,
    ]);

    $request->setContainer(app());

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->value)->toBe($json)
        ->and($data->type)->toBe('json')
        ->and($data->isPublic)->toBeTrue();
});

test('it preserves null values when transforming to setting data', function () {
    $request = new UpdateSettingRequest();

    $request->merge([
        'group' => 'general',
        'key' => 'optional_value',
        'value' => null,
        'type' => 'string',
    ]);

    $request->setContainer(app());

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->value)->toBeNull();
});
