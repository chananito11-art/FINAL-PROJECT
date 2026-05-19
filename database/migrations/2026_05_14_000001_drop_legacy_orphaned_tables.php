<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Drop all legacy/orphaned tables that are no longer part of the system.
     * Order matters: drop child tables (with FKs) before parent tables.
     */
    public function up(): void
    {
        // Disable FK checks so we can drop in any order safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. Drop FK column on vehicles before dropping categories
        if (Schema::hasColumn('vehicles', 'category_id')) {
            Schema::table('vehicles', function ($table) {
                // Drop the FK constraint then the column
                try { $table->dropForeign(['category_id']); } catch (\Exception $e) {}
                $table->dropColumn('category_id');
            });
        }

        // 2. Drop legacy/orphaned tables
        Schema::dropIfExists('vehicle_requirements');  // never used in any controller
        Schema::dropIfExists('rental_transactions');   // replaced by payments table
        Schema::dropIfExists('booking_accessory');     // accessories pivot — no UI
        Schema::dropIfExists('accessories');           // no customer-facing usage
        Schema::dropIfExists('customers');             // replaced by users + roles
        Schema::dropIfExists('employees');             // replaced by users + roles
        Schema::dropIfExists('retailcosts');           // Product model legacy table
        Schema::dropIfExists('categories');            // removed from vehicles above

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse is intentionally left minimal — data is gone.
     */
    public function down(): void
    {
        // Re-add category_id to vehicles (nullable, no FK since categories is gone)
        if (!Schema::hasColumn('vehicles', 'category_id')) {
            Schema::table('vehicles', function ($table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('id');
            });
        }
    }
};
