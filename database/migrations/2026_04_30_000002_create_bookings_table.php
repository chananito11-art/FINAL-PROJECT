<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            // Snapshot of customer details at time of booking
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('drivers_license_number');
            $table->date('pickup_date');
            $table->date('return_date');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending_payment',
                'awaiting_verification',
                'confirmed',
                'rejected',
                'ongoing',
                'completed',
                'cancelled',
            ])->default('pending_payment');
            $table->timestamp('terms_agreed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
