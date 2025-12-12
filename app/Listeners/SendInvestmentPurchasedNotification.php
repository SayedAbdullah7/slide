<?php

namespace App\Listeners;

use App\Events\InvestmentCreated;
use App\Notifications\InvestmentPurchasedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendInvestmentPurchasedNotification
{
    /**
     * Handle the event.
     */
    public function handle(InvestmentCreated $event): void
    {
        $investment = $event->investment;
        $user = $investment->investor->user;

        if ($user) {
            // Prevent duplicate notifications within 5 seconds
            // منع الإشعارات المكررة خلال 5 ثواني
            $cacheKey = "investment_notification_sent_{$investment->id}_{$user->id}";

            if (Cache::has($cacheKey)) {
                Log::warning('Duplicate investment notification prevented', [
                    'investment_id' => $investment->id,
                    'user_id' => $user->id,
                    'event' => 'InvestmentCreated'
                ]);
                return;
            }

            // Set cache lock for 5 seconds to prevent duplicates
            Cache::put($cacheKey, true, 2);

            $user->notify(new InvestmentPurchasedNotification($investment));

            Log::info('Investment created notification sent', [
                'investment_id' => $investment->id,
                'user_id' => $user->id,
                'shares' => $investment->shares
            ]);
        }
    }
}



