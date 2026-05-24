<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->string('plate_number')->unique()->nullable();
            $table->enum('type', ['Sedan', 'SUV', 'Pickup Truck', 'Van', 'Hatchback', 'Crossover'])->default('Sedan');
            $table->enum('transmission', ['Automatic', 'Manual'])->default('Automatic');
            $table->enum('fuel', ['Gasoline', 'Diesel', 'Electric', 'Hybrid'])->default('Gasoline');
            $table->unsignedTinyInteger('capacity')->default(5);
            $table->decimal('price_per_day', 10, 2);
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable'])->default('available');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
