<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_profile_id',
        'vehicle_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'drivers_license_number',
        'pickup_date',
        'return_date',
        'actual_return_date',
        'total_amount',
        'discount_amount',
        'late_fee',
        'security_deposit',
        'security_deposit_status',
        'refueling_fee',
        'status',
        'terms_agreed_at',
        'rejection_reason',
        'admin_notes',
        'hold_expires_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'pickup_date'        => 'date',
        'return_date'        => 'date',
        'actual_return_date' => 'datetime',
        'total_amount'       => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'late_fee'           => 'decimal:2',
        'security_deposit'   => 'decimal:2',
        'refueling_fee'      => 'decimal:2',
        'terms_agreed_at'    => 'datetime',
        'hold_expires_at'    => 'datetime',
        'cancelled_at'       => 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────────────────────
    const STATUS_AWAITING_APPROVAL    = 'awaiting_approval';
    const STATUS_PENDING_PAYMENT      = 'pending_payment';
    const STATUS_AWAITING_VERIFICATION = 'awaiting_verification';
    const STATUS_PARTIAL_PAID         = 'partial_paid';
    const STATUS_FULLY_PAID           = 'fully_paid';
    const STATUS_CONFIRMED             = 'confirmed';
    const STATUS_REJECTED              = 'rejected';
    const STATUS_ONGOING               = 'ongoing';
    const STATUS_COMPLETED             = 'completed';
    const STATUS_CANCELLED             = 'cancelled';
    const STATUS_NO_SHOW               = 'no_show';

    // ── Relationships ─────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment() // Kept for backward compatibility if needed for single-payment views
    {
        return $this->hasOne(Payment::class)->latest();
    }



    public function cancelledBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelled_by');
    }

    public function guestProfile()
    {
        return $this->belongsTo(GuestProfile::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }



    public function rental()
    {
        return $this->hasOne(Rental::class);
    }



    public function isHoldExpired(): bool
    {
        return $this->hold_expires_at && $this->hold_expires_at->isPast();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->where('status', 'verified')->sum('amount');
        }
        return (float) $this->payments()->where('status', 'verified')->sum('amount');
    }

    public function getBalanceAmountAttribute(): float
    {
        $base = max(0, (float) $this->total_amount - $this->paid_amount);
        
        $extraFees = (float) $this->late_fee + (float) $this->refueling_fee;
        
        if ($extraFees > 0 && in_array($this->security_deposit_status, ['held', 'held_for_deduction'])) {
            $uncoveredFees = max(0, $extraFees - (float)$this->security_deposit);
            return $base + $uncoveredFees;
        }
        
        return $base;
    }

    public function getDurationInDaysAttribute(): int
    {
        return $this->pickup_date->diffInDays($this->return_date) ?: 1;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'awaiting_approval'     => ['label' => 'Awaiting Approval',      'color' => 'blue'],
            'pending_payment'       => ['label' => 'Pending Payment',       'color' => 'yellow'],
            'awaiting_verification' => ['label' => 'Awaiting Verification', 'color' => 'blue'],
            'partial_paid'          => ['label' => 'Partial Paid',          'color' => 'orange'],
            'fully_paid'            => ['label' => 'Fully Paid',            'color' => 'green'],
            'confirmed'             => ['label' => 'Confirmed',             'color' => 'green'],
            'rejected'              => ['label' => 'Rejected',              'color' => 'red'],
            'ongoing'               => ['label' => 'Ongoing',               'color' => 'orange'],
            'completed'             => ['label' => 'Completed',             'color' => 'gray'],
            'cancelled'             => ['label' => 'Cancelled',             'color' => 'red'],
            'no_show'               => ['label' => 'No Show',               'color' => 'red'],
            default                 => ['label' => ucfirst($this->status),  'color' => 'gray'],
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
