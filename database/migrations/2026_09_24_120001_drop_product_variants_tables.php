<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * product_attribute_values (with the newly added attribute_id column) is
     * this app's single Product Specification/Variant pivot — the separate
     * product_variants / product_variant_attribute_values tables from an
     * earlier, now-abandoned Variants UI are dropped. Both are empty except
     * for 121 auto-generated placeholder rows in product_variants (no real
     * user-entered data), confirmed with the user before dropping.
     *
     * stock_transaction_items.product_variant_id and
     * product_stocks.product_variant_id currently reference product_variants
     * — their foreign keys are dropped here too so this migration can run.
     * The columns themselves are left in place (still empty tables) since
     * how Stock Transaction should key its line items going forward is a
     * separate decision the user will give direction on later.
     */
    public function up(): void
    {
        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });

        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
    }

    public function down(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('variant_name')->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
        });

        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_variant_id', 'attribute_value_id'], 'pvav_variant_attr_value_unique');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });
    }
};
