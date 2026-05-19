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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Operational Dates
            $table->dateTime('pickup_date');
            $table->dateTime('expected_return_date');
            $table->dateTime('actual_return_date')->nullable();
            
            // Operational Metrics
            $table->unsignedInteger('pickup_odometer');
            $table->unsignedInteger('return_odometer')->nullable();
            $table->unsignedInteger('pickup_fuel'); // Percentage
            $table->unsignedInteger('return_fuel')->nullable();
            
            // Financials (Post-Rental)
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('refueling_fee', 10, 2)->default(0);
            $table->decimal('damage_fee', 10, 2)->default(0);
            
            $table->enum('status', ['active', 'completed', 'overdue'])->default('active');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
