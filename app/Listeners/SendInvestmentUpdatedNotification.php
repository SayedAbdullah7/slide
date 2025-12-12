<?php

namespace App\Listeners;

use App\Events\InvestmentUpdated;
use App\Notifications\InvestmentPurchasedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendInvestmentUpdatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(InvestmentUpdated $event): void
    {
        // Only send notification if shares were added
        if ($event->updateType === 'shares_added') {
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
                        'update_type' => $event->updateType
                    ]);
                    return;
                }

                // Set cache lock for 5 seconds to prevent duplicates
                Cache::put($cacheKey, true, 5);

                // Refresh investment to get updated values
                $investment->refresh();

                // Send notification with updated investment data (isUpdate = true)
                $user->notify(new InvestmentPurchasedNotification($investment, true));

                Log::info('Investment update notification sent', [
                    'investment_id' => $investment->id,
                    'user_id' => $user->id,
                    'shares' => $investment->shares
                ]);
            }
        }
    }
}

