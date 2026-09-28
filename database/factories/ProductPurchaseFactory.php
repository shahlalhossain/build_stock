<?php

namespace Database\Factories;

use App\Models\ProductPurchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPurchase>
 */
class ProductPurchaseFactory extends Factory
{
    protected $model = ProductPurchase::class;

    protected static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $creatorId = User::factory()->create()->id;

        return [
            'code' => 'PUR-'.str_pad((string) (++static::$sequence), 4, '0', STR_PAD_LEFT),
            'requisition_id' => null,
            'store_id' => Store::factory(),
            'supplier_id' => Supplier::factory(),
            'transaction_date' => now()->toDateString(),
            'invoice_number' => 'INV-'.$this->faker->unique()->numerify('####'),
            'total_amount' => 0,
            'net_amount' => 0,
            'payment_status' => ProductPurchase::PAYMENT_STATUS_UNPAID,
            'paid_amount' => 0,
            'status' => ProductPurchase::STATUS_PENDING,
            'is_active' => true,
            'created_by' => $creatorId,
            'updated_by' => $creatorId,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => ProductPurchase::STATUS_APPROVED]);
    }
}
