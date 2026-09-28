<?php

namespace Tests\Feature;

use App\Exceptions\GeneralException;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductRequisition;
use App\Models\ProductRequisitionItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProductPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPurchaseAgainstRequisitionTest extends TestCase
{
    use RefreshDatabase;

    protected ProductPurchaseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ProductPurchaseService::class);
        $this->actingAs(User::factory()->create());
    }

    protected function makeApprovedRequisitionWithTwoItems(): ProductRequisition
    {
        $requisition = ProductRequisition::factory()->approved()->create();

        // Each Product's Requisition/Purchase Line uses the Product's own Base
        // Unit, so UnitConversionService::toBaseUnit() needs no Conversion row.
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        ProductRequisitionItem::factory()->create([
            'product_requisition_id' => $requisition->id,
            'product_id' => $productA->id,
            'unit_id' => $productA->unit_id,
            'quantity' => 100,
        ]);

        ProductRequisitionItem::factory()->create([
            'product_requisition_id' => $requisition->id,
            'product_id' => $productB->id,
            'unit_id' => $productB->unit_id,
            'quantity' => 100,
        ]);

        return $requisition->fresh('items');
    }

    protected function purchasePayloadFor(ProductRequisition $requisition, array $itemOverrides = []): array
    {
        $store = Store::factory()->create();
        $supplier = Supplier::factory()->create();

        return [
            'requisition_id' => $requisition->id,
            'store_id' => $store->id,
            'supplier_id' => $supplier->id,
            'transaction_date' => now()->toDateString(),
            'invoice_number' => 'INV-'.uniqid(),
            'items' => $itemOverrides,
        ];
    }

    public function test_requisition_available_for_purchase_lists_approved_requisitions_with_remaining_quantity(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();

        $available = ProductRequisition::query()->availableForPurchase()->pluck('id');

        $this->assertTrue($available->contains($requisition->id));
    }

    public function test_pending_requisition_is_not_available_for_purchase(): void
    {
        $requisition = ProductRequisition::factory()->create(); // default status = pending

        ProductRequisitionItem::factory()->create([
            'product_requisition_id' => $requisition->id,
            'quantity' => 100,
        ]);

        $available = ProductRequisition::query()->availableForPurchase()->pluck('id');

        $this->assertFalse($available->contains($requisition->id));
    }

    public function test_user_can_purchase_a_partial_subset_of_requisition_products(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        [$itemA, $itemB] = $requisition->items;

        // Only Purchase Product A (skip Product B entirely) — partial Line Selection.
        $payload = $this->purchasePayloadFor($requisition, [
            [
                'requisition_item_id' => $itemA->id,
                'product_id' => $itemA->product_id,
                'unit_id' => $itemA->unit_id,
                'quantity' => 50,
                'unit_cost' => 10,
            ],
        ]);

        $purchase = $this->service->storePurchase($payload);

        $this->assertCount(1, $purchase->items);
        $this->assertEquals(50, $purchase->items->first()->quantity);

        // Remaining Quantity is unaffected until the Purchase is Approved.
        $this->assertEquals(100, $itemA->fresh()->remaining_quantity);
    }

    public function test_remaining_quantity_only_decreases_after_purchase_is_approved(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        $item = $requisition->items->first();

        $payload = $this->purchasePayloadFor($requisition, [
            [
                'requisition_item_id' => $item->id,
                'product_id' => $item->product_id,
                'unit_id' => $item->unit_id,
                'quantity' => 40,
                'unit_cost' => 10,
            ],
        ]);

        $purchase = $this->service->storePurchase($payload);

        $this->assertEquals(100, $item->fresh()->remaining_quantity, 'Pending Purchase must not Reserve Quantity.');

        $this->service->updatePurchaseStatus($purchase->id, ProductPurchase::STATUS_APPROVED);

        $this->assertEquals(60, $item->fresh()->remaining_quantity, 'Approved Purchase must Decrement Remaining Quantity.');
    }

    public function test_purchase_creation_rejects_quantity_exceeding_remaining(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        $item = $requisition->items->first();

        $payload = $this->purchasePayloadFor($requisition, [
            [
                'requisition_item_id' => $item->id,
                'product_id' => $item->product_id,
                'unit_id' => $item->unit_id,
                'quantity' => 150, // Exceeds the Requisitioned 100.
                'unit_cost' => 10,
            ],
        ]);

        $this->expectException(GeneralException::class);

        $this->service->storePurchase($payload);
    }

    public function test_purchase_creation_rejects_product_not_in_requisition(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        $foreignItem = ProductRequisitionItem::factory()->create(); // Belongs to a Different Requisition.

        $payload = $this->purchasePayloadFor($requisition, [
            [
                'requisition_item_id' => $foreignItem->id,
                'product_id' => $foreignItem->product_id,
                'unit_id' => $foreignItem->unit_id,
                'quantity' => 10,
                'unit_cost' => 10,
            ],
        ]);

        $this->expectException(GeneralException::class);

        $this->service->storePurchase($payload);
    }

    public function test_second_pending_purchase_is_rejected_at_approval_if_it_would_overfulfill(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        $item = $requisition->items->first();

        // Two Pending Purchases, each Requesting 60 out of 100 — neither is
        // Blocked at Creation (Pending does not Reserve), but only ONE may Approve.
        $firstPurchase = $this->service->storePurchase($this->purchasePayloadFor($requisition, [
            ['requisition_item_id' => $item->id, 'product_id' => $item->product_id, 'unit_id' => $item->unit_id, 'quantity' => 60, 'unit_cost' => 10],
        ]));

        $secondPurchase = $this->service->storePurchase($this->purchasePayloadFor($requisition, [
            ['requisition_item_id' => $item->id, 'product_id' => $item->product_id, 'unit_id' => $item->unit_id, 'quantity' => 60, 'unit_cost' => 10],
        ]));

        $this->service->updatePurchaseStatus($firstPurchase->id, ProductPurchase::STATUS_APPROVED);

        $this->expectException(GeneralException::class);

        $this->service->updatePurchaseStatus($secondPurchase->id, ProductPurchase::STATUS_APPROVED);
    }

    public function test_requisition_drops_off_the_list_once_every_item_is_fully_purchased_and_approved(): void
    {
        $requisition = $this->makeApprovedRequisitionWithTwoItems();
        [$itemA, $itemB] = $requisition->items;

        foreach ([$itemA, $itemB] as $item) {
            $purchase = $this->service->storePurchase($this->purchasePayloadFor($requisition, [
                ['requisition_item_id' => $item->id, 'product_id' => $item->product_id, 'unit_id' => $item->unit_id, 'quantity' => 100, 'unit_cost' => 10],
            ]));

            $this->service->updatePurchaseStatus($purchase->id, ProductPurchase::STATUS_APPROVED);
        }

        $available = ProductRequisition::query()->availableForPurchase()->pluck('id');

        $this->assertFalse($available->contains($requisition->id));
    }
}
