<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One-time Data Migration: copies the existing Singular manager_id/
     * storekeeper_id Assignments on `stores` into the new `store_user` Pivot,
     * so no existing Assignment is lost when the UI switches to Multi-Select.
     * stores.manager_id/storekeeper_id are intentionally left in place (not
     * dropped here) — removing them is a separate, later Cleanup Migration
     * once the Pivot-based UI has been verified in Production.
     */
    public function up(): void
    {
        $now = now();

        $managers = DB::table('stores')
            ->whereNotNull('manager_id')
            ->get(['id as store_id', 'manager_id as user_id']);

        $storekeepers = DB::table('stores')
            ->whereNotNull('storekeeper_id')
            ->get(['id as store_id', 'storekeeper_id as user_id']);

        $rows = $managers->map(fn ($row) => [
            'store_id' => $row->store_id,
            'user_id' => $row->user_id,
            'role_type' => 'manager',
            'created_at' => $now,
            'updated_at' => $now,
        ])->concat($storekeepers->map(fn ($row) => [
            'store_id' => $row->store_id,
            'user_id' => $row->user_id,
            'role_type' => 'storekeeper',
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        foreach ($rows->chunk(500) as $chunk) {
            DB::table('store_user')->insertOrIgnore($chunk->all());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data-only Migration — nothing structural to reverse. Re-running up()
        // after a rollback of this step would simply re-insert the same rows
        // (insertOrIgnore), so this is intentionally a No-Op.
    }
};
