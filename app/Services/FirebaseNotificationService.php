<?php

namespace App\Services;

use App\Models\User;
use App\Models\FcmToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->initializeFirebase();
    }

    /**
     * Initialize Firebase messaging
     */
    protected function initializeFirebase()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials_path'))
                ->withProjectId(config('firebase.project_id'));

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase messaging', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        if (!$user->hasNotificationsEnabled()) {
            Log::info('User has notifications disabled', ['user_id' => $user->id]);
            return ['success' => false, 'message' => 'User notifications are disabled'];
        }

        $tokens = $user->activeFcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::warning('No active FCM tokens found for user', ['user_id' => $user->id]);
            return ['success' => false, 'message' => 'No active tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::whereIn('user_id', $userIds)
            ->whereHas('user', function ($query) {
                $query->where('notifications_enabled', true);
            })
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::warning('No active FCM tokens found for users', ['user_ids' => $userIds]);
            return ['success' => false, 'message' => 'No active tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to specific FCM tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No tokens provided'];
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ]));

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            // Send to each token individually for better error handling
            foreach ($tokens as $token) {
                try {
                    $result = $this->messaging->send($message->withChangedTarget('token', $token));
                    $results[] = ['token' => $token, 'success' => true, 'message_id' => $result];
                    $successCount++;

                    // Mark token as used
                    FcmToken::where('token', $token)->update(['last_used_at' => now()]);

                } catch (MessagingException $e) {
                    $results[] = ['token' => $token, 'success' => false, 'error' => $e->getMessage()];
                    $failureCount++;
                    Log::error('Failed to send Firebase notification', [
                        'error' => $e->getMessage(),
                        'token' => $e,
                    ]);

                    // If token is invalid, deactivate it
                    if ($this->isInvalidTokenError($e)) {
                        FcmToken::where('token', $token)->update(['is_active' => false]);
                        Log::warning('Deactivated invalid FCM token', ['token' => $token, 'error' => $e->getMessage()]);
                    }
                }
            }

            Log::info('Firebase notification sent', [
                'total_tokens' => count($tokens),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'title' => $title,
                'body' => $body,
            ]);

            return [
                'success' => $successCount > 0,
                'message' => "Sent to {$successCount} devices, {$failureCount} failed",
                'results' => $results,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send Firebase notification', [
                'error' => $e->getMessage(),
                'title' => $title,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to all active users
     */
    public function sendToAllUsers(string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::active()
            ->whereHas('user', function ($query) {
                $query->where('notifications_enabled', true);
            })
            ->pluck('token')
            ->toArray();

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send investment opportunity available notification
     */
    public function sendInvestmentOpportunityAvailableNotification(User $user, $opportunity, $reminder = null): array
    {
        $title = 'فرصة استثمارية متاحة الآن!';
        $body = "الفرصة الاستثمارية '{$opportunity->name}' متاحة الآن للاستثمار";

        $data = [
            'type' => 'investment_opportunity_available',
            'opportunity_id' => (string) $opportunity->id,
            'opportunity_name' => $opportunity->name,
            'opportunity_start_date' => $opportunity->offering_start_date?->toISOString(),
            'reminder_id' => $reminder ? (string) $reminder->id : null,
            'click_action' => 'investment_opportunity',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Send reminder notification
     */
    public function sendReminderNotification(User $user, $opportunity, $reminder = null): array
    {
        $title = 'تذكير: فرصة استثمارية متاحة';
        $body = "الفرصة الاستثمارية '{$opportunity->name}' التي كنت تنتظرها متاحة الآن للاستثمار";

        $data = [
            'type' => 'investment_reminder',
            'opportunity_id' => (string) $opportunity->id,
            'opportunity_name' => $opportunity->name,
            'reminder_id' => $reminder ? (string) $reminder->id : null,
            'click_action' => 'investment_opportunity',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Check if error indicates invalid token
     */
    protected function isInvalidTokenError(MessagingException $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'registration-token-not-registered') ||
               str_contains($message, 'invalid-registration-token') ||
               str_contains($message, 'mismatched-credential');
    }

    /**
     * Clean up invalid tokens
     */
    public function cleanupInvalidTokens(): int
    {
        // This would typically be called by a scheduled command
        // to clean up tokens that have been marked as inactive
        return FcmToken::where('is_active', false)
            ->where('last_used_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        return [
            'total_tokens' => FcmToken::count(),
            'active_tokens' => FcmToken::active()->count(),
            'inactive_tokens' => FcmToken::where('is_active', false)->count(),
            'tokens_by_platform' => FcmToken::active()
                ->selectRaw('platform, count(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
        ];
    }
}
