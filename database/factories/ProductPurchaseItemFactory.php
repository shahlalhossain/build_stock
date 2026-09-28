<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductPurchaseItem;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPurchaseItem>
 */
class ProductPurchaseItemFactory extends Factory
{
    protected $model = ProductPurchaseItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_purchase_id' => ProductPurchase::factory(),
            'requisition_item_id' => null,
            'product_id' => Product::factory(),
            'unit_id' => ProductUnit::factory(),
            'quantity' => 1,
            'unit_cost' => 10,
            'line_total' => 10,
        ];
    }
}
