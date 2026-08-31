<?php

use App\Models\Setting;
use App\Services\Settings\SettingService;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    $this->service = app(SettingService::class);

    $this->manager = new SettingsManager(
        $this->service,
    );
});

/*
|--------------------------------------------------------------------------
| Basic Retrieval
|--------------------------------------------------------------------------
*/

test('it can get an existing setting value', function () {
    $this->service->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
    );

    expect(
        $this->manager->get('general.site_name')
    )->toBe('Dynamic CMS');
});

test('it returns default value when setting does not exist', function () {
    expect(
        $this->manager->get(
            'general.non_existent_setting',
            'Default Value',
        )
    )->toBe('Default Value');
});

test('it returns null when setting does not exist and no default is provided', function () {
    expect(
        $this->manager->get('general.non_existent_setting')
    )->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Type Safety
|--------------------------------------------------------------------------
*/

test('it preserves boolean type when retrieving from cache', function () {
    $this->service->set(
        'general.maintenance_mode',
        true,
        'boolean',
    );

    $value = $this->manager->get(
        'general.maintenance_mode'
    );

    expect($value)->toBeTrue();

    // Second read must come from cache and preserve type.
    $cachedValue = $this->manager->get(
        'general.maintenance_mode'
    );

    expect($cachedValue)->toBeTrue();
});

test('it preserves integer type when retrieving from cache', function () {
    $this->service->set(
        'general.items_per_page',
        20,
        'integer',
    );

    $value = $this->manager->get(
        'general.items_per_page'
    );

    expect($value)->toBe(20);

    // Cached value must remain an integer.
    $cachedValue = $this->manager->get(
        'general.items_per_page'
    );

    expect($cachedValue)->toBe(20);
});

test('it preserves json values when retrieving from cache', function () {
    $data = [
        'theme' => 'light',
        'features' => [
            'comments' => true,
            'search' => true,
        ],
    ];

    $this->service->set(
        'general.site_configuration',
        $data,
        'json',
    );

    expect(
        $this->manager->get('general.site_configuration')
    )->toBe($data);

    // Ensure cached JSON remains an array.
    expect(
        $this->manager->get('general.site_configuration')
    )->toBe($data);
});

/*
|--------------------------------------------------------------------------
| Cache Behaviour
|--------------------------------------------------------------------------
*/

test('it caches setting values after first retrieval', function () {
    $this->service->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
    );

    $firstValue = $this->manager->get(
        'general.site_name'
    );

    expect($firstValue)->toBe('Dynamic CMS');

    // Change database directly.
    Setting::query()
        ->where('group', 'general')
        ->where('key', 'site_name')
        ->update([
            'value' => 'Changed Directly In Database',
        ]);

    // Cached value should still be returned.
    $cachedValue = $this->manager->get(
        'general.site_name'
    );

    expect($cachedValue)->toBe('Dynamic CMS');
});

test('it clears cached setting after updating through manager', function () {
    $this->manager->set(
        'general.site_name',
        'Old Name',
        'string',
    );

    // Populate cache.
    expect(
        $this->manager->get('general.site_name')
    )->toBe('Old Name');

    // Update through manager.
    $this->manager->set(
        'general.site_name',
        'New Name',
        'string',
    );

    expect(
        $this->manager->get('general.site_name')
    )->toBe('New Name');
});

test('it clears cached setting after deleting through manager', function () {
    $this->manager->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
    );

    // Populate cache.
    expect(
        $this->manager->get('general.site_name')
    )->toBe('Dynamic CMS');

    $deleted = $this->manager->delete(
        'general.site_name'
    );

    expect($deleted)->toBeTrue();

    expect(
        $this->manager->get(
            'general.site_name',
            'Fallback Value',
        )
    )->toBe('Fallback Value');
});

test('deleting a non existent setting does not affect other cached settings', function () {
    $this->manager->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
    );

    // Populate cache.
    $this->manager->get('general.site_name');

    $deleted = $this->manager->delete(
        'general.non_existent_setting'
    );

    expect($deleted)->toBeFalse();

    expect(
        $this->manager->get('general.site_name')
    )->toBe('Dynamic CMS');
});

/*
|--------------------------------------------------------------------------
| Default Value Cache Safety
|--------------------------------------------------------------------------
*/

test('default values are not permanently cached for missing settings', function () {
    $firstValue = $this->manager->get(
        'general.site_name',
        'Default Site',
    );

    expect($firstValue)->toBe('Default Site');

    // Create the actual setting after first retrieval.
    $this->service->set(
        'general.site_name',
        'Real Site Name',
        'string',
    );

    // Must return real database value, not stale default.
    expect(
        $this->manager->get(
            'general.site_name',
            'Another Default',
        )
    )->toBe('Real Site Name');
});

/*
|--------------------------------------------------------------------------
| Group Retrieval
|--------------------------------------------------------------------------
*/

test('it can retrieve settings from a group', function () {
    $this->service->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
    );

    $this->service->set(
        'general.site_description',
        'A dynamic content management system',
        'string',
    );

    $this->service->set(
        'seo.meta_title',
        'SEO Title',
        'string',
    );

    $settings = $this->manager->group('general');

    expect($settings)->toHaveCount(2);

    expect(
        $settings->pluck('key')->all()
    )->toContain(
        'site_name',
        'site_description',
    );
});

test('it returns empty collection for a group that does not exist', function () {
    expect(
        $this->manager->group('non_existent_group')
    )->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| Public Settings Security
|--------------------------------------------------------------------------
*/

test('it returns only public settings', function () {
    $this->service->set(
        'general.site_name',
        'Dynamic CMS',
        'string',
        true,
    );

    $this->service->set(
        'security.secret_key',
        'super-secret-value',
        'string',
        false,
    );

    $publicSettings = $this->manager->public();

    expect($publicSettings)->toHaveCount(1);

    expect(
        $publicSettings->pluck('key')->all()
    )->toContain('site_name');

    expect(
        $publicSettings->pluck('key')->all()
    )->not->toContain('secret_key');
});

test('private settings are never returned by public settings query', function () {
    $this->service->set(
        'security.turnstile_secret_key',
        'secret-value',
        'string',
        false,
    );

    $this->service->set(
        'security.api_secret',
        'another-secret',
        'string',
        false,
    );

    expect(
        $this->manager->public()
    )->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| Explicit Cache Invalidation
|--------------------------------------------------------------------------
*/

test('it can explicitly forget a cached setting', function () {
    $this->service->set(
        'general.site_name',
        'Original Name',
        'string',
    );

    // Populate cache.
    $this->manager->get('general.site_name');

    // Change database directly.
    Setting::query()
        ->where('group', 'general')
        ->where('key', 'site_name')
        ->update([
            'value' => 'Updated Directly',
        ]);

    // Still cached.
    expect(
        $this->manager->get('general.site_name')
    )->toBe('Original Name');

    $forgotten = $this->manager->forget(
        'general.site_name'
    );

    expect($forgotten)->toBeTrue();

    // Must now retrieve the new database value.
    expect(
        $this->manager->get('general.site_name')
    )->toBe('Updated Directly');
});
