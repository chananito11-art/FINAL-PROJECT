<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM(
            'awaiting_approval',
            'pending_payment',
            'awaiting_verification',
            'confirmed',
            'rejected',
            'ongoing',
            'completed',
            'cancelled',
            'no_show'
        ) DEFAULT 'awaiting_approval'");
    }

    public function down(): void
    {
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
};
