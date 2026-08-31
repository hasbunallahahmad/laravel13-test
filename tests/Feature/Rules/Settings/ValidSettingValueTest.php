<?php

use App\Rules\Settings\ValidSettingValue;
use Illuminate\Support\Facades\Validator;

test('it passes validation for a valid string value', function () {
    $validator = Validator::make(
        [
            'value' => 'Website Arpus',
        ],
        [
            'value' => [
                new ValidSettingValue('string'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it passes validation for a valid integer value', function () {
    $validator = Validator::make(
        [
            'value' => 20,
        ],
        [
            'value' => [
                new ValidSettingValue('integer'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid integer value', function () {
    $validator = Validator::make(
        [
            'value' => 'not-an-integer',
        ],
        [
            'value' => [
                new ValidSettingValue('integer'),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
});

test('it passes validation for boolean true', function () {
    $validator = Validator::make(
        [
            'value' => true,
        ],
        [
            'value' => [
                new ValidSettingValue('boolean'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it passes validation for boolean false', function () {
    $validator = Validator::make(
        [
            'value' => false,
        ],
        [
            'value' => [
                new ValidSettingValue('boolean'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid boolean value', function () {
    $validator = Validator::make(
        [
            'value' => 'not-a-boolean',
        ],
        [
            'value' => [
                new ValidSettingValue('boolean'),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
});

// test('it passes validation for a valid url', function () {
//     $validator = Validator::make(
//         [
//             'value' => 'https://www.example.com',
//         ],
//         [
//             'value' => [
//                 new ValidSettingValue('url'),
//             ],
//         ],
//     );

//     expect($validator->passes())->toBeTrue();
// });

// test('it rejects an invalid url', function () {
//     $validator = Validator::make(
//         [
//             'value' => 'this-is-not-a-url',
//         ],
//         [
//             'value' => [
//                 new ValidSettingValue('url'),
//             ],
//         ],
//     );

//     expect($validator->fails())->toBeTrue();
// });

test('it passes validation for the null setting type', function () {
    $validator = Validator::make(
        [
            'value' => null,
        ],
        [
            'value' => [
                new ValidSettingValue('null'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it rejects a non-null value for the null setting type', function () {
    $validator = Validator::make(
        [
            'value' => 'Website Arpus',
        ],
        [
            'value' => [
                new ValidSettingValue('null'),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
});

test('it passes validation for a valid json string', function () {
    $validator = Validator::make(
        [
            'value' => '{"theme":"dark","items":10}',
        ],
        [
            'value' => [
                new ValidSettingValue('json'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it rejects an invalid json string', function () {
    $validator = Validator::make(
        [
            'value' => '{"theme":"dark"',
        ],
        [
            'value' => [
                new ValidSettingValue('json'),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
});

test('it allows null values', function () {
    $validator = Validator::make(
        [
            'value' => null,
        ],
        [
            'value' => [
                'nullable',
                new ValidSettingValue('string'),
            ],
        ],
    );

    expect($validator->passes())->toBeTrue();
});

test('it rejects an unsupported setting type', function () {
    $validator = Validator::make(
        [
            'value' => 'anything',
        ],
        [
            'value' => [
                new ValidSettingValue('unsupported'),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
});
