<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_automatically_generates_uuid(): void
    {
        $setting = Setting::factory()->create();

        $this->assertNotEmpty($setting->uuid);

        $this->assertTrue(
            \Illuminate\Support\Str::isUuid($setting->uuid),
        );
    }

    public function test_setting_uuids_are_unique(): void
    {
        $first = Setting::factory()->create();
        $second = Setting::factory()->create();

        $this->assertNotSame(
            $first->uuid,
            $second->uuid,
        );
    }

    public function test_setting_uses_uuid_for_route_model_binding(): void
    {
        $setting = new Setting();

        $this->assertSame(
            'uuid',
            $setting->getRouteKeyName(),
        );
    }

    public function test_boolean_setting_returns_boolean_true(): void
    {
        $setting = Setting::factory()
            ->boolean(true)
            ->create();

        $this->assertTrue(
            $setting->typed_value,
        );
    }

    public function test_boolean_setting_returns_boolean_false(): void
    {
        $setting = Setting::factory()
            ->boolean(false)
            ->create();

        $this->assertFalse(
            $setting->typed_value,
        );
    }

    public function test_integer_setting_returns_integer(): void
    {
        $setting = Setting::factory()
            ->integer(42)
            ->create();

        $this->assertSame(
            42,
            $setting->typed_value,
        );
    }

    public function test_json_setting_returns_array(): void
    {
        $data = [
            'facebook' => 'https://facebook.com/example',
            'instagram' => 'https://instagram.com/example',
        ];

        $setting = Setting::factory()
            ->json($data)
            ->create();

        $this->assertSame(
            $data,
            $setting->typed_value,
        );
    }

    public function test_private_setting_is_not_public(): void
    {
        $setting = Setting::factory()
            ->private()
            ->create();

        $this->assertFalse(
            $setting->is_public,
        );
    }

    public function test_duplicate_group_and_key_are_rejected(): void
    {
        Setting::factory()->create([
            'group' => 'general',
            'key' => 'site_name',
        ]);

        $this->expectException(QueryException::class);

        Setting::factory()->create([
            'group' => 'general',
            'key' => 'site_name',
        ]);
    }

    public function test_same_key_can_exist_in_different_groups(): void
    {
        $first = Setting::factory()->create([
            'group' => 'general',
            'key' => 'title',
        ]);

        $second = Setting::factory()->create([
            'group' => 'seo',
            'key' => 'title',
        ]);

        $this->assertDatabaseHas('settings', [
            'id' => $first->id,
        ]);

        $this->assertDatabaseHas('settings', [
            'id' => $second->id,
        ]);
    }
}
