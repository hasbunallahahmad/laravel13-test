<?php

declare(strict_types=1);

namespace App\ContentBlocks\Validation;

use App\Models\Media;
use InvalidArgumentException;

final class MediaReferenceValidator
{
    public function validate(string $mediaUuid): void
    {
        if (! $this->exists($mediaUuid)) {
            throw new InvalidArgumentException(
                "Media with UUID [{$mediaUuid}] was not found.",
            );
        }
    }

    /**
     * @param array<int, string> $mediaUuids
     */
    public function validateMany(array $mediaUuids): void
    {
        if ($mediaUuids === []) {
            return;
        }

        $foundCount = Media::query()
            ->whereIn('uuid', $mediaUuids)
            ->count();

        if ($foundCount !== count(array_unique($mediaUuids))) {
            throw new InvalidArgumentException(
                'One or more media references were not found.',
            );
        }
    }

    private function exists(string $mediaUuid): bool
    {
        return Media::query()
            ->where('uuid', $mediaUuid)
            ->exists();
    }
}
