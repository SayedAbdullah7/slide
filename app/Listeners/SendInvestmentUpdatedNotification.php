<?php

namespace App\Listeners;

use App\Events\InvestmentUpdated;
use App\Notifications\InvestmentPurchasedNotification;

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
                // Refresh investment to get updated values
                $investment->refresh();

                // Send notification with updated investment data (isUpdate = true)
                $user->notify(new InvestmentPurchasedNotification($investment, true));
            }
        }
    }
}

