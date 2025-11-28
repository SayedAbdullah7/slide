<?php

namespace App\Notifications;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProfitDistributedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $investment;
    protected $opportunity;
    protected $profitAmount;
    protected $balance;

    /**
     * Create a new notification instance.
     */
    public function __construct(Investment $investment, float $profitAmount, float $balance)
    {
        $this->investment = $investment;
        $this->opportunity = $investment->opportunity;
        $this->profitAmount = $profitAmount;
        $this->balance = $balance;
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
            'type' => 'profit_distributed',
            'investment_id' => $this->investment->id,
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'profit_amount' => $this->profitAmount,
            'balance' => $this->balance,
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
                'type' => 'profit_distributed',
                'investment_id' => (string) $this->investment->id,
                'opportunity_id' => (string) $this->opportunity->id,
                'opportunity_name' => $this->opportunity->name,
                'profit_amount' => (string) $this->profitAmount,
                'balance' => (string) $this->balance,
                'click_action' => 'investment_details',
            ],
        ];
    }

    /**
     * Get notification title
     */
    public function getTitle(): string
    {
        return 'تم توزيع أرباح الاستثمار';
    }

    /**
     * Get notification body
     */
    public function getBody(): string
    {
        return "تم توزيع أرباح من فرصة '{$this->opportunity->name}' بمبلغ " . number_format($this->profitAmount, 2) . " ريال. الرصيد الحالي: " . number_format($this->balance, 2) . " ريال";
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return [
            'type' => 'profit_distributed',
            'investment_id' => $this->investment->id,
            'opportunity_id' => $this->opportunity->id,
            'opportunity_name' => $this->opportunity->name,
            'profit_amount' => $this->profitAmount,
            'balance' => $this->balance,
        ];
    }
}








