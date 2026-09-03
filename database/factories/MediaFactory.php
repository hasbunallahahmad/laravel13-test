<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
final class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement([
            'jpg',
            'png',
            'pdf',
        ]);

        $uuid = (string) Str::uuid();

        return [
            'uuid' => $uuid,

            'original_name' => fake()->words(
                3,
                true,
            ) . '.' . $extension,

            'file_name' => $uuid . '.' . $extension,

            'disk' => 'public',

            'path' => 'media/2026/09',

            'mime_type' => match ($extension) {
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
            },

            'extension' => $extension,

            'size' => fake()->numberBetween(
                1_000,
                5_000_000,
            ),

            'metadata' => [],

            'alt_text' => fake()->sentence(),

            'caption' => fake()->sentence(),

            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Create an image media.
     */
    public function image(): static
    {
        return $this->state(fn(): array => [
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'metadata' => [
                'width' => 1920,
                'height' => 1080,
            ],
        ]);
    }

    /**
     * Create a PDF media.
     */
    public function pdf(): static
    {
        return $this->state(fn(): array => [
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'metadata' => [],
        ]);
    }
}
