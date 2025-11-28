<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;
    protected $clickAction;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, array $data = [], ?string $clickAction = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->clickAction = $clickAction;
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
            'type' => 'custom',
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'click_action' => $this->clickAction,
        ];
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => array_merge([
                'type' => 'custom',
                'click_action' => $this->clickAction ?? 'home',
            ], $this->data),
        ];
    }

    /**
     * Get notification title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get notification body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get notification data
     */
    public function getData(): array
    {
        return $this->data;
    }
}








