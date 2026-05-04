<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Accessors ────────────────────────────────────────────────────────────
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ── Role helpers (convenience wrappers for Spatie) ────────────────────────
    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
}
