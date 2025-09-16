<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['phone','code','is_used','expires_at'];
    protected $casts = ['is_used' => 'boolean', 'expires_at' => 'datetime'];

    public function isExpired(): bool {
        return $this->expires_at->isPast();
    }
}
