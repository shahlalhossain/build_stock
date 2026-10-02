<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_id' => ProductUnit::factory(),
            'name' => fake()->unique()->words(3, true),
            'code' => fake()->unique()->bothify('PRD-####'),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'has_variants' => false,
            'is_active' => true,
            'status' => 'approved',
        ];
    }
}
