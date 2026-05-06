<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_submitted', 10, 2)->nullable()->after('amount');
            $table->string('gcash_transaction_reference_number')->unique()->nullable()->after('reference_code');
            $table->string('gcash_account_name')->nullable()->after('gcash_transaction_reference_number');
            $table->string('gcash_number_used')->nullable()->after('gcash_account_name');
            $table->boolean('amount_matched')->nullable()->after('amount_submitted');
            $table->text('admin_payment_notes')->nullable()->after('rejection_reason');
            $table->boolean('refund_issued')->default(false)->after('admin_payment_notes');
            $table->timestamp('refund_issued_at')->nullable()->after('refund_issued');
            $table->string('refund_gcash_reference')->nullable()->after('refund_issued_at');
            $table->text('refund_notes')->nullable()->after('refund_gcash_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'amount_submitted',
                'gcash_transaction_reference_number',
                'gcash_account_name',
                'gcash_number_used',
                'amount_matched',
                'admin_payment_notes',
                'refund_issued',
                'refund_issued_at',
                'refund_gcash_reference',
                'refund_notes',
            ]);
        });
    }
};
