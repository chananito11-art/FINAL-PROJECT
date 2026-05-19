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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['pickup', 'return'])->default('pickup');
            
            $table->unsignedInteger('odometer_reading');
            $table->string('fuel_level'); // e.g. Full, 3/4, 1/2, 1/4, Empty
            
            $table->string('exterior_condition'); // Good, Scratched, Dented, etc.
            $table->string('interior_condition'); // Clean, Stained, etc.
            
            $table->json('images_paths')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
