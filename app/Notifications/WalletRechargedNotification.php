<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class WalletRechargedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $balance;
    protected $paymentMethod;

    /**
     * Create a new notification instance.
     */
    public function __construct(float $amount, float $balance, string $paymentMethod = 'payment_gateway')
    {
        $this->amount = $amount;
        $this->balance = $balance;
        $this->paymentMethod = $paymentMethod;
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
            'type' => 'wallet_recharged',
            'amount' => $this->amount,
            'balance' => $this->balance,
            'payment_method' => $this->paymentMethod,
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
                'type' => 'wallet_recharged',
                'amount' => (string) $this->amount,
                'balance' => (string) $this->balance,
                'payment_method' => $this->paymentMethod,
                'click_action' => 'wallet',
            ],
        ];
    }

    /**
     * Get notification title
     */
    public function getTitle(): string
    {
        return 'تم شحن المحفظة بنجاح';
    }

    /**
     * Get notification body
     */
    public function getBody(): string
    {
        return "تم إضافة " . number_format($this->amount, 2) . " ريال إلى محفظتك. الرصيد الحالي: " . number_format($this->balance, 2) . " ريال";
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return [
            'type' => 'wallet_recharged',
            'amount' => $this->amount,
            'balance' => $this->balance,
            'payment_method' => $this->paymentMethod,
        ];
    }
}








