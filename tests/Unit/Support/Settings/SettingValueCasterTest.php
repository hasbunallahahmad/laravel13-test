<?php

use App\Support\Settings\SettingValueCaster;
use InvalidArgumentException;

test('it supports expected setting types', function () {
    expect(SettingValueCaster::supports('string'))->toBeTrue();
    expect(SettingValueCaster::supports('integer'))->toBeTrue();
    expect(SettingValueCaster::supports('boolean'))->toBeTrue();
    expect(SettingValueCaster::supports('json'))->toBeTrue();
    expect(SettingValueCaster::supports('null'))->toBeTrue();

    expect(SettingValueCaster::supports('unknown'))->toBeFalse();
});

test('it casts string values correctly', function () {
    expect(
        SettingValueCaster::get('Dynamic CMS', 'string')
    )->toBe('Dynamic CMS');

    expect(
        SettingValueCaster::set('Dynamic CMS', 'string')
    )->toBe('Dynamic CMS');
});

test('it casts integer values correctly', function () {
    expect(
        SettingValueCaster::get('123', 'integer')
    )->toBe(123);

    expect(
        SettingValueCaster::set(123, 'integer')
    )->toBe('123');
});

test('it rejects invalid integer values', function () {
    expect(fn() => SettingValueCaster::get(
        'abc',
        'integer',
    ))->toThrow(InvalidArgumentException::class);

    expect(fn() => SettingValueCaster::set(
        '123',
        'integer',
    ))->toThrow(InvalidArgumentException::class);
});

test('it casts boolean values correctly', function () {
    expect(
        SettingValueCaster::get('1', 'boolean')
    )->toBeTrue();

    expect(
        SettingValueCaster::get('0', 'boolean')
    )->toBeFalse();

    expect(
        SettingValueCaster::set(true, 'boolean')
    )->toBe('1');

    expect(
        SettingValueCaster::set(false, 'boolean')
    )->toBe('0');
});

test('it rejects invalid boolean values', function () {
    expect(fn() => SettingValueCaster::get(
        'yes',
        'boolean',
    ))->toThrow(InvalidArgumentException::class);

    expect(fn() => SettingValueCaster::set(
        'true',
        'boolean',
    ))->toThrow(InvalidArgumentException::class);
});

test('it encodes and decodes json correctly', function () {
    $value = [
        'site_name' => 'Dynamic CMS',
        'enabled' => true,
        'items' => [
            'one',
            'two',
        ],
    ];

    $encoded = SettingValueCaster::set(
        $value,
        'json',
    );

    expect($encoded)->toBeString();

    expect(
        SettingValueCaster::get(
            $encoded,
            'json',
        )
    )->toBe($value);
});

test('it rejects invalid json', function () {
    expect(fn() => SettingValueCaster::get(
        '{invalid-json}',
        'json',
    ))->toThrow(InvalidArgumentException::class);
});

test('null type always returns null', function () {
    expect(
        SettingValueCaster::get(
            'anything',
            'null',
        )
    )->toBeNull();

    expect(
        SettingValueCaster::set(
            'anything',
            'null',
        )
    )->toBeNull();
});

test('it rejects unsupported setting types', function () {
    expect(fn() => SettingValueCaster::get(
        'value',
        'unknown',
    ))->toThrow(InvalidArgumentException::class);
});
