<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(8),
            'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&auto=format&fit=crop&q=80',
            'link_url' => null,
            'is_active' => true,
            'order' => 0,
        ];
    }
}
