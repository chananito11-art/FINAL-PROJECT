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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('refueling_fee_per_liter', 10, 2)->default(0)->after('late_penalty_per_hour');
            $table->unsignedInteger('fuel_capacity_liters')->default(50)->after('refueling_fee_per_liter');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('security_deposit', 10, 2)->default(0)->after('late_fee');
            $table->enum('security_deposit_status', ['pending', 'held', 'released', 'forfeited'])->default('pending')->after('security_deposit');
            $table->decimal('refueling_fee', 10, 2)->default(0)->after('security_deposit_status');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['refueling_fee_per_liter', 'fuel_capacity_liters']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['security_deposit', 'security_deposit_status', 'refueling_fee']);
        });
    }
};
