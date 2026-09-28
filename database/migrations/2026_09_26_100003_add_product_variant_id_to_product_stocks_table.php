<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only Present on Databases where this Index/Column Predates the Variant
        // Refactor — a Fresh Migrate never creates them under these Names.
        $indexNames = collect(Schema::getIndexes('product_stocks'))->pluck('name');
        $oldUniqueIndexName = collect(['product_stocks_product_variant_store_unique', 'product_stocks_product_id_store_id_unique'])
            ->first(fn ($name) => $indexNames->contains($name));

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
        });

        if (Schema::hasColumn('product_stocks', 'product_attribute_value_id')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_attribute_value_id');
            });
        }

        // Create the New Composite Unique BEFORE Dropping the Old one, so the
        // product_id Foreign Key always has a Supporting Index to fall back on.
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'product_variant_id', 'store_id'], 'product_stocks_product_variant_store_unique');
        });

        if ($oldUniqueIndexName && $oldUniqueIndexName !== 'product_stocks_product_variant_store_unique') {
            Schema::table('product_stocks', function (Blueprint $table) use ($oldUniqueIndexName) {
                $table->dropUnique($oldUniqueIndexName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique('product_stocks_product_variant_store_unique');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')->nullable()->after('product_id')
                ->constrained('product_attribute_values');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'product_attribute_value_id', 'store_id'], 'product_stocks_product_variant_store_unique');
        });
    }
};
