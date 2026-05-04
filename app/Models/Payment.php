<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'reference_code',
        'screenshot_path',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'verified_at' => 'datetime',
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
