<?php

namespace Database\Seeders;

use App\Services\Settings\SettingsManager;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Seed the application's default settings.
     */
    public function run(SettingsManager $settings): void
    {
        /*
        |--------------------------------------------------------------------------
        | General Website Settings
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'general.site_name',
            'Dinas Arsip dan Perpustakaan Kota Semarang',
        );

        $settings->set(
            'general.site_description',
            'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang',
        );

        $settings->set(
            'general.site_email',
            null,
            'null',
        );

        $settings->set(
            'general.site_phone',
            null,
            'null',
        );

        /*
        |--------------------------------------------------------------------------
        | Website Appearance
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'appearance.logo',
            null,
            'null',
        );

        $settings->set(
            'appearance.favicon',
            null,
            'null',
        );

        /*
        |--------------------------------------------------------------------------
        | Website Maintenance
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'system.maintenance_mode',
            false,
            'boolean',
        );

        /*
        |--------------------------------------------------------------------------
        | Social Media
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'social.facebook',
            null,
            'null',
            true,
        );

        $settings->set(
            'social.instagram',
            null,
            'null',
            true,
        );

        $settings->set(
            'social.youtube',
            null,
            'null',
            true,
        );
    }
}
