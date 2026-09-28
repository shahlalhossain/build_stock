<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductRequisition;
use App\Models\ProductRequisitionItem;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductRequisitionItem>
 */
class ProductRequisitionItemFactory extends Factory
{
    protected $model = ProductRequisitionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_requisition_id' => ProductRequisition::factory(),
            'product_id' => Product::factory(),
            'unit_id' => ProductUnit::factory(),
            'quantity' => 100,
        ];
    }
}
