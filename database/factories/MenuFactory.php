<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
final class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->unique()->slug(),
            'label' => fake()->words(2, true),
            'type' => 'url',
            'url' => fake()->url(),
            'route_name' => null,
            'target' => '_self',
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function url(?string $url = null): static
    {
        return $this->state(fn(): array => [
            'type' => 'url',
            'url' => $url ?? fake()->url(),
            'route_name' => null,
        ]);
    }

    public function route(string $routeName = 'admin.dashboard'): static
    {
        return $this->state([
            'type' => 'route',
            'url' => null,
            'route_name' => $routeName,
        ]);
    }

    public function childOf(Menu $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    public function active(): static
    {
        return $this->state([
            'is_active' => true,
        ]);
    }
}
