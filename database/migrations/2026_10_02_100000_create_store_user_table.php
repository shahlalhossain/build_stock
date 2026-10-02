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
        Schema::create('store_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Which Slot this User fills at this Store — a User may hold either or
            // both Slots at the same Store, and may be attached to several Stores.
            // This is purely a Labeling/Display + "which Store to Auto-Assign on
            // Create" concern; it does NOT gate what the User can DO — that is
            // governed entirely by their Spatie Role/Permission, independent of
            // which Slot(s) they fill here (see Phase 1 and the Store-Access Plan).
            $table->enum('role_type', ['manager', 'storekeeper']);

            $table->timestamps();

            $table->unique(['store_id', 'user_id', 'role_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_user');
    }
};
