<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'status',
        'last_login_at',
        'created_by',
        'loyalty_points',
        'verification_status',
        'id_expiration_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'last_login_at'      => 'datetime',
            'id_expiration_date' => 'date',
            'loyalty_points'     => 'integer',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function isVerified(): bool
    {
        if ($this->id_expiration_date && $this->id_expiration_date->isPast()) {
            if ($this->verification_status !== 'expired') {
                $this->update(['verification_status' => 'expired']);
            }
            return false;
        }
        return $this->verification_status === 'verified';
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

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdEmployees()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
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
