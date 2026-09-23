<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * For every existing product, create one default ProductVariant so all
     * 121 existing products are immediately usable in Stock Transaction
     * without requiring a manual variant to be added first. products.sku
     * and product_attribute_values are untouched by this migration — a
     * product's own sku/specs and its variants' sku/specs are independent.
     * The default variant's sku reuses the product's unique `code` (e.g.
     * PRD-0001), which is guaranteed unique and never null.
     */
    public function up(): void
    {
        $products = DB::table('products')->select('id', 'code')->get();

        foreach ($products as $product) {
            DB::table('product_variants')->insert([
                'product_id' => $product->id,
                'sku' => $product->code,
                'variant_name' => null,
                'unit_price' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Irreversible: the split between which variant "was the default one"
     * for a given product is not something we can safely reconstruct.
     */
    public function down(): void
    {
        DB::table('product_variants')->truncate();
    }
};
