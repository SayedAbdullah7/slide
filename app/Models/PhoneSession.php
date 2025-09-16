<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneSession extends Model
{
    protected $fillable = ['phone', 'token', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];

    public function isExpired(): bool {
        return $this->expires_at->isPast();
    }
}
