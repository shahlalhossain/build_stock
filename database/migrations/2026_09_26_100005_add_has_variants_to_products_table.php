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
        // Already Present on a Fresh Database — create_products_table Creates it
        // Directly. Only Needed on Databases Migrated before that Column Existed.
        if (! Schema::hasColumn('products', 'has_variants')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_variants')->default(false)->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-Op: create_products_table owns this Column — Dropping it here would
        // also break Databases where this Migration never had to run its up().
    }
};
