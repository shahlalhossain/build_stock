<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // `supplier_type_id` has no DB Foreign Key Constraint (no supplier_types
            // Table exists yet) — an arbitrary integer satisfies the NOT NULL Column.
            'supplier_type_id' => 1,
            'code' => fake()->unique()->bothify('SUP-####'),
            'name' => fake()->unique()->company(),
            'is_active' => true,
            'status' => 'approved',
        ];
    }
}
