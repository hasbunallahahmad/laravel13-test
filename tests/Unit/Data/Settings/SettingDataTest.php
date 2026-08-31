<?php

use App\Data\Settings\SettingData;
use InvalidArgumentException;

test('it can create a setting data object', function () {
    $data = new SettingData(
        group: 'general',
        key: 'site_name',
        value: 'Website Arpus',
        type: 'string',
        isPublic: true,
    );

    expect($data->group)->toBe('general')
        ->and($data->key)->toBe('site_name')
        ->and($data->value)->toBe('Website Arpus')
        ->and($data->type)->toBe('string')
        ->and($data->isPublic)->toBeTrue();
});

test('it generates the correct full key', function () {
    $data = new SettingData(
        group: 'general',
        key: 'site_name',
        value: 'Website Arpus',
    );

    expect($data->fullKey())
        ->toBe('general.site_name');
});

test('it can create setting data from a full key', function () {
    $data = SettingData::fromKey(
        'general.site_name',
        'Website Arpus',
        'string',
        true,
    );

    expect($data->group)->toBe('general')
        ->and($data->key)->toBe('site_name')
        ->and($data->value)->toBe('Website Arpus')
        ->and($data->type)->toBe('string')
        ->and($data->isPublic)->toBeTrue();
});

test('it uses default values when creating from a full key', function () {
    $data = SettingData::fromKey(
        'general.site_name',
        'Website Arpus',
    );

    expect($data->type)->toBe('string')
        ->and($data->isPublic)->toBeFalse();
});

test('it rejects an unsupported setting type', function () {
    new SettingData(
        group: 'general',
        key: 'site_name',
        value: 'Website Arpus',
        type: 'unsupported',
    );
})->throws(
    InvalidArgumentException::class,
    'Unsupported setting type: unsupported',
);

test('it rejects an empty group', function () {
    new SettingData(
        group: '',
        key: 'site_name',
        value: 'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'Setting group cannot be empty.',
);

test('it rejects a whitespace only group', function () {
    new SettingData(
        group: '   ',
        key: 'site_name',
        value: 'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'Setting group cannot be empty.',
);

test('it rejects an empty key', function () {
    new SettingData(
        group: 'general',
        key: '',
        value: 'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'Setting key cannot be empty.',
);

test('it rejects a whitespace only key', function () {
    new SettingData(
        group: 'general',
        key: '   ',
        value: 'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'Setting key cannot be empty.',
);

test('it rejects a full key without a separator', function () {
    SettingData::fromKey(
        'site_name',
        'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'The setting key must use group.key format.',
);

test('it rejects a full key with multiple separators', function () {
    SettingData::fromKey(
        'general.site.name',
        'Website Arpus',
    );
})->throws(
    InvalidArgumentException::class,
    'The setting key must use group.key format.',
);
