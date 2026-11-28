<?php

declare(strict_types=1);

namespace App\Services\Homepage;

use InvalidArgumentException;

final class HomepageSectionSettingsValidator
{
    public function __construct(
        private readonly HomepageSectionRegistry $registry,
    ) {}

    /**
     * Memvalidasi pengaturan berdasarkan section yang terdaftar.
     *
     * Untuk sementara, schema mengikuti default_settings pada Registry.
     * Key yang belum didefinisikan tidak diperbolehkan.
     *
     * @param mixed $settings
     * @return array<string, mixed>
     */
    public function validate(string $sectionKey, mixed $settings): array
    {
        $definition = $this->registry->get($sectionKey);

        if ($definition === null) {
            throw new InvalidArgumentException(
                "Unknown homepage section: {$sectionKey}."
            );
        }

        if (! is_array($settings)) {
            throw new InvalidArgumentException(
                "Settings for homepage section [{$sectionKey}] must be an array."
            );
        }

        $allowedSettings = $definition['default_settings'];

        $unknownKeys = array_diff(
            array_keys($settings),
            array_keys($allowedSettings),
        );

        if ($unknownKeys !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown settings for homepage section [%s]: %s.',
                    $sectionKey,
                    implode(', ', $unknownKeys),
                ),
            );
        }

        return $settings;
    }
}
