<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('hold_expires_at')->nullable()->after('terms_agreed_at');
            $table->string('cancellation_reason')->nullable()->after('admin_notes');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['hold_expires_at', 'cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
