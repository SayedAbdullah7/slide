<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseChannel
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable instanceof User) {
            return;
        }

        if (method_exists($notification, 'toFirebase')) {
            $firebaseData = $notification->toFirebase($notifiable);
        } else {
            // Fallback to default structure
            $firebaseData = [
                'title' => $notification->getTitle(),
                'body' => $notification->getBody(),
                'data' => $notification->getData(),
            ];
        }

        try {
            $result = $this->firebaseService->sendToUser(
                $notifiable,
                $firebaseData['title'] ?? '',
                $firebaseData['body'] ?? '',
                $firebaseData['data'] ?? []
            );

            // Log result
            if (!$result['success']) {
                Log::warning('Firebase notification failed', [
                    'user_id' => $notifiable->id,
                    'notification_type' => get_class($notification),
                    'result' => $result,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Firebase notification exception', [
                'user_id' => $notifiable->id,
                'notification_type' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }
}








