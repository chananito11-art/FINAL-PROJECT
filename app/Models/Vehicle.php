<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'model',
        'year',
        'plate_number',
        'type',
        'transmission',
        'fuel',
        'capacity',
        'price_per_day',
        'status',
        'description',
        'image',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'year'          => 'integer',
        'capacity'      => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1200&q=80';
    }
}
