<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'amount_submitted',
        'payment_method',
        'reference_code',
        'gcash_transaction_reference_number',
        'gcash_account_name',
        'gcash_number_used',
        'screenshot_path',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'amount_matched',
        'admin_payment_notes',
        'refund_issued',
        'refund_issued_at',
        'refund_gcash_reference',
        'refund_notes',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'amount_submitted' => 'decimal:2',
        'verified_at'      => 'datetime',
        'refund_issued_at' => 'datetime',
        'amount_matched'   => 'boolean',
        'refund_issued'    => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getScreenshotUrlAttribute(): string
    {
        return asset('storage/' . $this->screenshot_path);
    }
}
