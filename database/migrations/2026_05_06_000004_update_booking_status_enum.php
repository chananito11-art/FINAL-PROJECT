<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // We use a raw query because changing ENUMs via standard Blueprint can be unreliable depending on the DB driver
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM(
                'awaiting_approval',
                'pending_payment',
                'awaiting_verification',
                'confirmed',
                'rejected',
                'ongoing',
                'completed',
                'cancelled'
            ) DEFAULT 'awaiting_approval'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM(
                'pending_payment',
                'awaiting_verification',
                'confirmed',
                'rejected',
                'ongoing',
                'completed',
                'cancelled'
            ) DEFAULT 'pending_payment'");
        }
    }
};
