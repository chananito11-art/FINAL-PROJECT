<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'drivers_license_number',
        'pickup_date',
        'return_date',
        'total_amount',
        'status',
        'terms_agreed_at',
        'rejection_reason',
        'admin_notes',
    ];

    protected $casts = [
        'pickup_date'    => 'date',
        'return_date'    => 'date',
        'total_amount'   => 'decimal:2',
        'terms_agreed_at'=> 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────────────────────
    const STATUS_PENDING_PAYMENT      = 'pending_payment';
    const STATUS_AWAITING_VERIFICATION = 'awaiting_verification';
    const STATUS_CONFIRMED             = 'confirmed';
    const STATUS_REJECTED              = 'rejected';
    const STATUS_ONGOING               = 'ongoing';
    const STATUS_COMPLETED             = 'completed';
    const STATUS_CANCELLED             = 'cancelled';

    // ── Relationships ─────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function requirements()
    {
        return $this->hasMany(VehicleRequirement::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getDurationInDaysAttribute(): int
    {
        return $this->pickup_date->diffInDays($this->return_date) ?: 1;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending_payment'       => ['label' => 'Pending Payment',       'color' => 'yellow'],
            'awaiting_verification' => ['label' => 'Awaiting Verification', 'color' => 'blue'],
            'confirmed'             => ['label' => 'Confirmed',             'color' => 'green'],
            'rejected'              => ['label' => 'Rejected',              'color' => 'red'],
            'ongoing'               => ['label' => 'Ongoing',               'color' => 'orange'],
            'completed'             => ['label' => 'Completed',             'color' => 'gray'],
            'cancelled'             => ['label' => 'Cancelled',             'color' => 'red'],
            default                 => ['label' => ucfirst($this->status),  'color' => 'gray'],
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
