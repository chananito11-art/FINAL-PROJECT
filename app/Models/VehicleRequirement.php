<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRequirement extends Model
{
    protected $fillable = ['booking_id', 'file_path', 'file_type'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
