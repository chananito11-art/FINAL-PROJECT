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
            $table->decimal('late_penalty_per_hour', 10, 2)->default(0)->after('price_per_day');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('late_fee', 10, 2)->default(0)->after('discount_amount');
            $table->dateTime('actual_return_date')->nullable()->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('late_penalty_per_hour');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['late_fee', 'actual_return_date']);
        });
    }
};
