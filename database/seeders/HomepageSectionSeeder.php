<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HomepageSection;
use App\Services\Homepage\HomepageSectionRegistry;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(HomepageSectionRegistry $registry): void
    {
        foreach ($registry->all() as $key => $definition) {
            HomepageSection::query()->firstOrCreate(
                ['key' => $key],
                [
                    'is_enabled' => $definition['default_enabled'],
                    'sort_order' => $definition['default_sort_order'],
                    'config_version' => $definition['config_version'],
                    'settings' => $definition['default_settings'],
                ],
            );
        }
    }
}
