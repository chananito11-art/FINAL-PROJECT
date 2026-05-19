<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_type',
        'description',
        'mileage_at_service',
        'service_date',
        'cost',
        'next_service_due_mileage',
        'next_service_due_date',
        'performed_by',
    ];

    protected $casts = [
        'service_date'          => 'date',
        'next_service_due_date' => 'date',
        'cost'                  => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }}
