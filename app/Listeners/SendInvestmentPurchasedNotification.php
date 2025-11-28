<?php

namespace App\Listeners;

use App\Events\InvestmentCreated;
use App\Notifications\InvestmentPurchasedNotification;

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
            $user->notify(new InvestmentPurchasedNotification($investment));
        }
    }
}



