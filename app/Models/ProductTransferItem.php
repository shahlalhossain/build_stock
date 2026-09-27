<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTransferItem extends Model
{
    protected $table = 'product_transfer_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_transfer_id',
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
            'product_transfer_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'unit_id' => 'integer',
            'quantity' => 'decimal:2',
        ];
    }

    public function productTransfer(): BelongsTo
    {
        return $this->belongsTo(ProductTransfer::class, 'product_transfer_id');
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
}
