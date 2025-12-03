<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_token',
        'masked_pan',
        'card_brand',
        'paymob_token_id',
        'paymob_order_id',
        'paymob_merchant_id',
        'is_default',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Note: card_token is NOT hidden because it's needed for Paymob API
     * It's only sent to Paymob server, not displayed in user-facing responses
     */

    /**
     * Get the user that owns the card
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get card display name (e.g., "Visa ending in 1234")
     */
    public function getCardDisplayNameAttribute(): string
    {
        $lastFour = substr($this->masked_pan, -4);
        return "{$this->card_brand} ending in {$lastFour}";
    }

    /**
     * Get last 4 digits of card
     */
    public function getLastFourAttribute(): string
    {
        return substr($this->masked_pan, -4);
    }

    /**
     * Scope to get only active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get or create card (prevent duplicates)
     */
    public static function getOrCreateCard(array $data): self
    {
        // Try to find existing card by token
        $card = self::where('user_id', $data['user_id'])
            ->where('card_token', $data['card_token'])
            ->first();

        if ($card) {
            // Card exists, update it if needed
            $card->update([
                'paymob_token_id' => $data['paymob_token_id'] ?? $card->paymob_token_id,
                'paymob_order_id' => $data['paymob_order_id'] ?? $card->paymob_order_id,
                'is_active' => true, // Reactivate if it was deactivated
            ]);

            return $card;
        }

        // Try to find by masked_pan (in case token changed)
        $card = self::where('user_id', $data['user_id'])
            ->where('masked_pan', $data['masked_pan'])
            ->first();

        if ($card) {
            // Update with new token
            $card->update([
                'card_token' => $data['card_token'],
                'paymob_token_id' => $data['paymob_token_id'] ?? $card->paymob_token_id,
                'paymob_order_id' => $data['paymob_order_id'] ?? $card->paymob_order_id,
                'is_active' => true,
            ]);

            return $card;
        }

        // Create new card
        $card = self::create($data);

        // If this is the user's first card, make it default
        $activeCardsCount = self::where('user_id', $data['user_id'])->active()->count();
        if ($activeCardsCount === 1) {
            $card->update(['is_default' => true]);
        }

        return $card;
    }
}
