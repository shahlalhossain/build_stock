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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_variants');

            $table->integer('category_id')->after('id');
            $table->integer('sub_category_id')->nullable()->after('category_id');
            $table->integer('brand_id')->nullable()->after('sub_category_id');
            $table->integer('unit_id')->nullable()->after('brand_id');
            $table->string('sku')->nullable()->unique()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'sub_category_id', 'brand_id', 'unit_id', 'sku']);
            $table->boolean('has_variants')->default(false);
        });
    }
};
