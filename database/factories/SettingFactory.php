<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->randomElement([
                'general',
                'seo',
                'social',
                'contact',
            ]),

            'key' => fake()->unique()->slug(3),

            'value' => fake()->sentence(),

            'type' => 'string',

            'is_public' => true,
        ];
    }

    /**
     * Create a boolean setting.
     */
    public function boolean(bool $value = true): static
    {
        return $this->state(fn(): array => [
            'value' => $value ? 'true' : 'false',
            'type' => 'boolean',
        ]);
    }

    /**
     * Create an integer setting.
     */
    public function integer(int $value = 1): static
    {
        return $this->state(fn(): array => [
            'value' => (string) $value,
            'type' => 'integer',
        ]);
    }

    /**
     * Create a JSON setting.
     *
     * @param array<string, mixed> $value
     */
    public function json(array $value = []): static
    {
        return $this->state(fn(): array => [
            'value' => json_encode($value),
            'type' => 'json',
        ]);
    }

    /**
     * Mark the setting as private.
     */
    public function private(): static
    {
        return $this->state(fn(): array => [
            'is_public' => false,
        ]);
    }
}
