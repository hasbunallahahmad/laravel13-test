<?php

declare(strict_types=1);

namespace App\Rules\Media;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

final class ValidImageContent implements ValidationRule
{
    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(
            new Driver(),
        );
    }

    /**
     * Run the validation rule.
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $mimeType = $value->getMimeType();

        if (! in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            return;
        }

        try {
            $this->manager->decodeSplFileInfo($value);
        } catch (\Throwable) {
            $fail(
                'File gambar tidak valid atau tidak dapat dibaca.',
            );
        }
    }
}
