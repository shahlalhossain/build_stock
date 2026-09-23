<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * product_attribute_values is this app's Product Specification/Variant
     * pivot. attribute_id is added directly on it (alongside
     * attribute_value_id) so a row's Attribute is readable without joining
     * through attribute_values — backfilled from each row's existing
     * attribute_value_id -> attribute_values.attribute_id.
     */
    public function up(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->foreignId('attribute_id')->nullable()->after('product_id')->constrained('attributes');
        });

        DB::statement('
            UPDATE product_attribute_values pav
            INNER JOIN attribute_values av ON av.id = pav.attribute_value_id
            SET pav.attribute_id = av.attribute_id
        ');

        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->foreignId('attribute_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropForeign(['attribute_id']);
            $table->dropColumn('attribute_id');
        });
    }
};
