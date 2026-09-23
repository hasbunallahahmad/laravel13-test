<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentBlockType;
use App\Models\Content;
use App\Models\ContentBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentBlock>
 */
class ContentBlockFactory extends Factory
{
    protected $model = ContentBlock::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'type' => ContentBlockType::TEXT,
            'data' => [
                'content' => fake()->paragraph(),
            ],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
