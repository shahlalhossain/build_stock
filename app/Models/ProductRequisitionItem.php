<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'product_requisition_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_requisition_id',
        'product_id',
        'product_variant_id',
        'unit_id',
        'quantity',
        'remarks',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'product_requisition_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'unit_id' => 'integer',
            'quantity' => 'decimal:2',
        ];
    }

    public function productRequisition(): BelongsTo
    {
        return $this->belongsTo(ProductRequisition::class, 'product_requisition_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(ProductPurchaseItem::class, 'requisition_item_id');
    }

    /**
     * Quantity already Fulfilled by Approved Purchases against this Line.
     * Pending/Rejected Purchases do NOT reserve or Consume Quantity.
     *
     * Uses the already Eager-Loaded 'purchaseItems.productPurchase' Relation
     * when available to avoid N+1 Queries on Listing Pages; falls back to a
     * fresh Query otherwise (e.g. when called on a single freshly-loaded Model).
     */
    public function getPurchasedQuantityAttribute(): float
    {
        if ($this->relationLoaded('purchaseItems')) {
            return (float) $this->purchaseItems
                ->filter(fn (ProductPurchaseItem $purchaseItem) => $purchaseItem->productPurchase?->status === ProductPurchase::STATUS_APPROVED)
                ->sum('quantity');
        }

        return (float) $this->purchaseItems()
            ->whereHas('productPurchase', function ($query) {
                $query->where('status', ProductPurchase::STATUS_APPROVED);
            })
            ->sum('quantity');
    }

    /**
     * Quantity still available to Purchase on this Line.
     */
    public function getRemainingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - $this->purchased_quantity);
    }
}
