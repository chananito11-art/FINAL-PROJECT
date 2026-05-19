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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('service_type'); // Oil Change, Tire Rotation, etc.
            $table->text('description')->nullable();
            $table->unsignedInteger('mileage_at_service');
            $table->date('service_date');
            $table->decimal('cost', 10, 2)->nullable();
            $table->unsignedInteger('next_service_due_mileage')->nullable();
            $table->date('next_service_due_date')->nullable();
            $table->string('performed_by')->nullable(); // Shop name or mechanic
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
