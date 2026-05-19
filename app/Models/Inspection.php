<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'odometer_reading',
        'fuel_level',
        'exterior_condition',
        'interior_condition',
        'images_paths',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'images_paths' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }}
