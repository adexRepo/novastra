<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'slug' => fn (array $attributes): string => Str::slug($attributes['name']).'-'.fake()->unique()->numberBetween(1, 99999),
            'sku' => fake()->unique()->bothify('NST-???-####'),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'price' => fake()->numberBetween(5000, 250000),
            'stock' => fake()->numberBetween(0, 100),
            'status' => 'ACTIVE',
            'featured' => false,
            'is_deleted' => false,
        ];
    }
}
