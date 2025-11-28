<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investor_id',
        'bank_id',
        'account_holder_name',
        'iban',
        'account_number',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the bank account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investor profile that owns the bank account
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    /**
     * Get the bank
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get masked account number for display (last 4 digits)
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        if ($this->account_number) {
            return '****' . substr($this->account_number, -4);
        }

        // Try to extract from IBAN if account_number is not set
        if ($this->iban && strlen($this->iban) >= 4) {
            return '****' . substr($this->iban, -4);
        }

        return '****';
    }

    /**
     * Scope to get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default account
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Set this account as default and unset others for the user/investor
     */
    public function setAsDefault(): void
    {
        $query = static::where('id', '!=', $this->id);

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }
        if ($this->investor_id) {
            $query->where('investor_id', $this->investor_id);
        }

        $query->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get bank name from related bank
     */
    public function getBankNameAttribute(): ?string
    {
        return $this->bank?->name_ar;
    }

    /**
     * Get bank name in English from related bank
     */
    public function getBankNameEnAttribute(): ?string
    {
        return $this->bank?->name_en;
    }

    /**
     * Get bank code from related bank
     */
    public function getBankCodeAttribute(): ?string
    {
        return $this->bank?->code;
    }
}
