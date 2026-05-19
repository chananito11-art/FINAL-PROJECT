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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('security_deposit_status', [
                'pending',
                'held',
                'released',
                'held_for_deduction',
                'settled',
                'refunded'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('security_deposit_status', [
                'pending',
                'held',
                'released',
                'forfeited'
            ])->default('pending')->change();
        });
    }
};
