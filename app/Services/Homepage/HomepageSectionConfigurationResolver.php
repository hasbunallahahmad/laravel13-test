<?php

declare(strict_types=1);

namespace App\Services\Homepage;

use App\Models\HomepageSection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class HomepageSectionConfigurationResolver
{
    public function __construct(
        private readonly HomepageSectionRegistry $registry,
        private readonly HomepageSectionSettingsValidator $settingsValidator,
    ) {}

    public function resolve(): array
    {
        $databaseSections = HomepageSection::query()
            ->get()
            ->keyBy('key');

        $sections = [];

        foreach ($this->registry->all() as $key => $definition) {
            $databaseSection = $databaseSections->get($key);

            $settings = $databaseSection?->settings
                ?? $definition['default_settings'];

            try {
                $settings = $this->settingsValidator->validate(
                    $key,
                    $settings,
                );
            } catch (InvalidArgumentException) {
                Log::warning('Invalid homepage section settings; registry defaults applied.', [
                    'section_key' => $key,
                    'config_version' => $databaseSection?->config_version
                        ?? $definition['config_version'],
                ]);

                $settings = $definition['default_settings'];
            }

            $sections[] = [
                'key' => $key,
                'is_enabled' => $databaseSection?->is_enabled
                    ?? $definition['default_enabled'],
                'sort_order' => $databaseSection?->sort_order
                    ?? $definition['default_sort_order'],
                'config_version' => $databaseSection?->config_version
                    ?? $definition['config_version'],
                'settings' => $settings,
            ];
        }

        $registryOrder = array_flip(array_keys($this->registry->all()));

        usort(
            $sections,
            static fn(array $a, array $b): int => ($a['sort_order'] <=> $b['sort_order'])
                ?: ($registryOrder[$a['key']] <=> $registryOrder[$b['key']]),
        );

        return $sections;
    }
}
