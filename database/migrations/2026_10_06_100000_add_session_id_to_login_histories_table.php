<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('login_histories', 'session_id')) {
            Schema::table('login_histories', function (Blueprint $table) {
                $table->string('session_id', 255)->nullable()->index()->after('device');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('login_histories', 'session_id')) {
            Schema::table('login_histories', function (Blueprint $table) {
                $table->dropColumn('session_id');
            });
        }
    }
};
