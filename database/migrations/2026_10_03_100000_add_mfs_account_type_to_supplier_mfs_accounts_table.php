<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('supplier_mfs_accounts', 'mfs_account_type')) {
            Schema::table('supplier_mfs_accounts', function (Blueprint $table) {
                $table->string('mfs_account_type', 20)->nullable()->after('mfs_operator_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('supplier_mfs_accounts', 'mfs_account_type')) {
            Schema::table('supplier_mfs_accounts', function (Blueprint $table) {
                $table->dropColumn('mfs_account_type');
            });
        }
    }
};
