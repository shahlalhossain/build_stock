<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $table = 'product_variants';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'unit_price',
        'is_active',
        'created_by',
        'updated_by',
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
            'product_id' => 'integer',
            'unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_variant_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attribute_values',
            'product_variant_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Build a display label for dropdowns, e.g. "PRD-0001 — 10Kg Bag" or
     * just "PRD-0001" when variant_name is blank (the common case for the
     * auto-backfilled default variants).
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->variant_name
            ? $this->sku.' — '.$this->variant_name
            : $this->sku;
    }

    /**
     * Build a label from this Variant's Attribute-Value combination, e.g.
     * "Color: Red - Size: Small", using the same groupBy('attribute.name')
     * convention as product/show.blade.php's Specifications table. Returns
     * an empty string when the Variant has no Attribute Values assigned
     * (true for every auto-backfilled default Variant until Part 1's UI
     * is used on it) — the caller decides the fallback display.
     */
    public function getAttributeValuesLabelAttribute(): string
    {
        return $this->attributeValues
            ->groupBy('attribute.name')
            ->map(fn ($values, $attributeName) => $attributeName.': '.$values->pluck('value')->implode(', '))
            ->implode(' - ');
    }
}
