<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        return [
            'type' => ContentType::ARTICLE,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->optional()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'status' => ContentStatus::DRAFT,
            'published_at' => null,
            'author_id' => User::factory(),
            'metadata' => null,
        ];
    }
}
