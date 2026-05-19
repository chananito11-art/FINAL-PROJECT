<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'year',
        'plate_number',
        'odometer',
        'maintenance_interval_km',
        'type',
        'transmission',
        'fuel',
        'capacity',
        'price_per_day',
        'late_penalty_per_hour',
        'refueling_fee_per_liter',
        'fuel_capacity_liters',
        'status',
        'description',
        'image',
    ];

    protected $casts = [
        'price_per_day'           => 'decimal:2',
        'late_penalty_per_hour'   => 'decimal:2',
        'refueling_fee_per_liter' => 'decimal:2',
        'fuel_capacity_liters'    => 'integer',
        'year'                    => 'integer',
        'capacity'                => 'integer',
        'odometer'                => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }


    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['available', 'rented']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function isAvailable(): bool
    {
        return in_array($this->status, ['available', 'rented']);
    }

    /**
     * Check if a vehicle is available for a given date range.
     * Excludes the given booking ID (useful for editing bookings).
     */
    public static function isAvailableForDates(int $vehicleId, Carbon $pickup, Carbon $return, ?int $excludeBookingId = null): bool
    {
        return !\App\Models\Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', [
                \App\Models\Booking::STATUS_AWAITING_APPROVAL, 
                \App\Models\Booking::STATUS_PENDING_PAYMENT, 
                \App\Models\Booking::STATUS_AWAITING_VERIFICATION, 
                \App\Models\Booking::STATUS_CONFIRMED, 
                \App\Models\Booking::STATUS_ONGOING
            ])
            ->where(function ($q) use ($pickup, $return) {
                // The occupied range is [pickup_date, (actual end) + 1 day]
                // If it is 'ongoing' and overdue, the effective return date stretches to NOW().
                $q->where('pickup_date', '<=', $return)
                  ->whereRaw("DATE_ADD(IF(status = 'ongoing', GREATEST(return_date, NOW()), return_date), INTERVAL 1 DAY) >= ?", [$pickup->toDateString()]);
            })
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1200&q=80';
    }
}
