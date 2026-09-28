<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Store::TYPE_STORE,
            'name' => fake()->unique()->company(),
            'code' => fake()->unique()->bothify('STR-####'),
            'is_active' => true,
            'status' => 'approved',
        ];
    }
}
