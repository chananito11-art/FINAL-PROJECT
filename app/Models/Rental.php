<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'user_id',
        'pickup_date',
        'expected_return_date',
        'actual_return_date',
        'pickup_odometer',
        'return_odometer',
        'pickup_fuel',
        'return_fuel',
        'late_fee',
        'refueling_fee',
        'damage_fee',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'pickup_date'          => 'datetime',
        'expected_return_date' => 'datetime',
        'actual_return_date'   => 'datetime',
        'late_fee'             => 'decimal:2',
        'refueling_fee'        => 'decimal:2',
        'damage_fee'           => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && now()->gt($this->expected_return_date);
    }}
