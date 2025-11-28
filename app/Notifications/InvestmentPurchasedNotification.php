<?php

namespace App\Notifications;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvestmentPurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $investment;
    protected $opportunity;
    protected $isUpdate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Investment $investment, bool $isUpdate = false)
    {
        $this->investment = $investment;
        $this->opportunity = $investment->opportunity;
        $this->isUpdate = $isUpdate;
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
            'type' => 'investment_purchased',
            'investment_id' => $this->investment->id,
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'shares' => $this->investment->shares,
            'amount' => $this->investment->total_investment,
            'is_update' => $this->isUpdate,
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
                'type' => 'investment_purchased',
                'investment_id' => (string) $this->investment->id,
                'opportunity_id' => (string) $this->opportunity->id,
                'opportunity_name' => $this->opportunity->name,
                'shares' => (string) $this->investment->shares,
                'amount' => (string) $this->investment->total_investment,
                'is_update' => $this->isUpdate,
                'click_action' => 'investment_details',
            ],
        ];
    }

    /**
     * Get notification title
     */
    public function getTitle(): string
    {
        return $this->isUpdate
            ? 'تم تحديث الاستثمار بنجاح'
            : 'تم شراء الاستثمار بنجاح';
    }

    /**
     * Get notification body
     */
    public function getBody(): string
    {
        if ($this->isUpdate) {
            return "تم إضافة شرائح إضافية لاستثمارك في فرصة '{$this->opportunity->name}'. إجمالي الأسهم الآن: {$this->investment->shares} سهم بإجمالي " . number_format($this->investment->total_investment, 2) . " ريال";
        }

        return "تم شراء {$this->investment->shares} سهم من فرصة '{$this->opportunity->name}' بمبلغ " . number_format($this->investment->total_investment, 2) . " ريال";
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return [
            'type' => 'investment_purchased',
            'investment_id' => $this->investment->id,
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'shares' => $this->investment->shares,
            'amount' => $this->investment->total_investment,
            'is_update' => $this->isUpdate,
        ];
    }
}



