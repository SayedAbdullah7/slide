<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CustomNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminNotificationController extends Controller
{
    /**
     * Send custom notification to specific users
     */
    public function sendToUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'sometimes|array',
            'click_action' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $users = User::whereIn('id', $request->user_ids)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users found',
            ], 404);
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                $user->notify(new CustomNotification(
                    $request->title,
                    $request->body,
                    $request->data ?? [],
                    $request->click_action
                ));
                $sentCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to send custom notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Notifications sent to {$sentCount} users, {$failedCount} failed",
            'data' => [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_users' => $users->count(),
            ],
        ]);
    }

    /**
     * Send custom notification to all active users
     */
    public function sendToAllUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'sometimes|array',
            'click_action' => 'sometimes|string|max:255',
            'profile_type' => ['sometimes', Rule::in(['investor', 'owner', 'all'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = User::active();

        // Filter by profile type if specified
        if ($request->profile_type && $request->profile_type !== 'all') {
            $query->where('active_profile_type', $request->profile_type);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active users found',
            ], 404);
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                $user->notify(new CustomNotification(
                    $request->title,
                    $request->body,
                    $request->data ?? [],
                    $request->click_action
                ));
                $sentCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to send custom notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Notifications sent to {$sentCount} users, {$failedCount} failed",
            'data' => [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_users' => $users->count(),
            ],
        ]);
    }

    /**
     * Send custom notification to investors
     */
    public function sendToInvestors(Request $request)
    {
        $request->merge(['profile_type' => 'investor']);
        return $this->sendToAllUsers($request);
    }

    /**
     * Send custom notification to owners
     */
    public function sendToOwners(Request $request)
    {
        $request->merge(['profile_type' => 'owner']);
        return $this->sendToAllUsers($request);
    }
}







