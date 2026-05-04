<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'ip_address',
        'url',
        'details',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper to quickly log an action ────────────────────────────────
    public static function log(string $action, ?string $modelType = null, ?int $modelId = null, ?string $details = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return;
        }

        static::create([
            'user_id'    => $user->id,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'ip_address' => request()->ip(),
            'url'        => request()->fullUrl(),
            'details'    => $details,
            'created_at' => now(),
        ]);
    }
}
