<?php

namespace Database\Factories;

use App\Models\ProductRequisition;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductRequisition>
 */
class ProductRequisitionFactory extends Factory
{
    protected $model = ProductRequisition::class;

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
            'code' => 'REQ-'.str_pad((string) (++static::$sequence), 4, '0', STR_PAD_LEFT),
            'store_id' => Store::factory(),
            'transaction_date' => now()->toDateString(),
            'status' => ProductRequisition::STATUS_PENDING,
            'is_active' => true,
            'created_by' => $creatorId,
            'updated_by' => $creatorId,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => ProductRequisition::STATUS_APPROVED]);
    }
}
