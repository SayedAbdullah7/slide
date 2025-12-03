<?php

namespace App\Events;

use App\Models\InvestmentOpportunity;
use App\Models\InvestmentOpportunityReminder;
use App\Services\FirebaseNotificationService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvestmentOpportunityAvailable
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public InvestmentOpportunity $opportunity;
    public InvestmentOpportunityReminder $reminder;

    /**
     * Create a new event instance.
     */
    public function __construct(InvestmentOpportunity $opportunity, InvestmentOpportunityReminder $reminder)
    {
        $this->opportunity = $opportunity;
        $this->reminder = $reminder;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('investment-opportunities'),
        ];
    }

    /**
     * Handle the event.
     */
    public function handle(): void
    {
        try {
            $user = $this->reminder->investorProfile->user;

            if ($user) {
                // Send notification using Laravel Notifications
                $user->notify(new \App\Notifications\InvestmentOpportunityAvailableNotification(
                    $this->opportunity,
                    $this->reminder
                ));
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending notification', [
                'user_id' => $this->reminder->investorProfile->user_id,
                'opportunity_id' => $this->opportunity->id,
                'reminder_id' => $this->reminder->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
