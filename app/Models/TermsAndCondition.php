<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    protected $fillable = ['content', 'updated_by'];

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Convenience ───────────────────────────────────────────────────────────
    public static function current(): ?self
    {
        return static::latest()->first();
    }
}
