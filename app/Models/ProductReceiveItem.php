<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReceiveItem extends Model
{
    protected $table = 'product_receive_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_receive_id',
        'transfer_item_id',
        'product_id',
        'product_variant_id',
        'unit_id',
        'received_quantity',
        'variance_remarks',
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
            'product_receive_id' => 'integer',
            'transfer_item_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'unit_id' => 'integer',
            'received_quantity' => 'decimal:2',
        ];
    }

    public function productReceive(): BelongsTo
    {
        return $this->belongsTo(ProductReceive::class, 'product_receive_id');
    }

    public function transferItem(): BelongsTo
    {
        return $this->belongsTo(ProductTransferItem::class, 'transfer_item_id');
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
