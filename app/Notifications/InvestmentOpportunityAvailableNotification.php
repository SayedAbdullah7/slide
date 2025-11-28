<?php

namespace App\Notifications;

use App\Models\InvestmentOpportunity;
use App\Models\InvestmentOpportunityReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvestmentOpportunityAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $opportunity;
    protected $reminder;

    /**
     * Create a new notification instance.
     */
    public function __construct(InvestmentOpportunity $opportunity, ?InvestmentOpportunityReminder $reminder = null)
    {
        $this->opportunity = $opportunity;
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'firebase'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'investment_opportunity_available',
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'reminder_id' => $this->reminder?->id,
            'start_date' => $this->opportunity->offering_start_date?->toISOString(),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
        ];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'data' => [
                'type' => 'investment_opportunity_available',
                'opportunity_id' => (string) $this->opportunity->id,
                'opportunity_name' => $this->opportunity->name,
                'opportunity_start_date' => $this->opportunity->offering_start_date?->toISOString(),
                'reminder_id' => $this->reminder ? (string) $this->reminder->id : null,
                'click_action' => 'investment_opportunity',
            ],
        ];
    }

    /**
     * Get notification title
     */
    public function getTitle(): string
    {
        return $this->reminder 
            ? 'تذكير: فرصة استثمارية متاحة'
            : 'فرصة استثمارية متاحة الآن!';
    }

    /**
     * Get notification body
     */
    public function getBody(): string
    {
        if ($this->reminder) {
            return "الفرصة الاستثمارية '{$this->opportunity->name}' التي كنت تنتظرها متاحة الآن للاستثمار";
        }
        
        return "الفرصة الاستثمارية '{$this->opportunity->name}' متاحة الآن للاستثمار";
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return [
            'type' => 'investment_opportunity_available',
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'reminder_id' => $this->reminder?->id,
        ];
    }
}








