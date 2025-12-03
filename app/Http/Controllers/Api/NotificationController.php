<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $type = $request->input('type');
        $read = $request->input('read'); // 'read', 'unread', or null for all

        $query = $user->notifications();

        if ($user->active_profile_type === User::PROFILE_OWNER) {
            $query->where('id','0');
        }

        // Filter by type
        if ($type) {
            $query->where('type', 'like', "%{$type}%");
        }

        // Filter by read status
        if ($read === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($read === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = [
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
            'notifications_enabled' => (bool) $user->notifications_enabled,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ];

        return $this->respondSuccessWithData('Notifications retrieved successfully', $data);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->respondError('Notification not found', 404);
        }

        $notification->markAsRead();

        return $this->respondSuccessWithData('Notification marked as read', [
            'notification' => new NotificationResource($notification),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return $this->respondSuccess('All notifications marked as read');
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->respondError('Notification not found', 404);
        }

        // Store notification data before deletion for response
        $notificationData = new NotificationResource($notification);
        $notification->delete();

        return $this->respondSuccessWithData('Notification deleted successfully', [
            'notification' => $notificationData,
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->delete();

        return $this->respondSuccess('All notifications deleted successfully');
    }

    /**
     * Get notification settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        return $this->respondSuccessWithData('Notification settings retrieved successfully', [
            'notifications_enabled' => (bool) $user->notifications_enabled,
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->notifications_enabled = $data['enabled'];
        $user->save();

        return $this->respondSuccessWithData('Notification settings updated successfully', [
            'notifications_enabled' => (bool) $user->notifications_enabled,
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return $this->respondSuccessWithData('Unread count retrieved', [
            'unread_count' => $count,
        ]);
    }

    /**
     * Get notification statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return $this->respondSuccessWithData('Notification statistics', $stats);
    }
}


