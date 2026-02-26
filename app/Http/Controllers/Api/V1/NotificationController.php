<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends BaseApiController
{
    protected string $version = 'v1';

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = $user->notifications();

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($perPage);

        // Transform notifications
        $notifications->getCollection()->transform(function ($notification) {
            return $this->transformNotification($notification);
        });

        return $this->paginatedResponse($notifications, 'Notifications retrieved successfully');
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return $this->successResponse([
            'unread_count' => $count
        ], 'Unread count retrieved successfully');
    }

    /**
     * Get recent notifications (for navbar/dropdown)
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);

        $notifications = $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return $this->transformNotification($notification);
            });

        $unreadCount = $user->unreadNotifications()->count();

        return $this->successResponse([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'has_more' => $user->notifications()->count() > $limit
        ], 'Recent notifications retrieved successfully');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->markAsRead();

        return $this->successResponse(
            $this->transformNotification($notification->fresh()),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return $this->successResponse(null, 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->update(['deleted_by' => $user->id]);
        $notification->delete();

        return $this->successResponse(null, 'Notification deleted successfully');
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user->readNotifications()->delete();

        return $this->successResponse([
            'deleted_count' => $count
        ], "{$count} read notifications deleted successfully");
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications();
        $count = $notifications->count();

        // For mass delete to trigger soft delete and update deleted_by, we need to loop or use update
        // But mass delete() on Eloquent relationship with SoftDeletes trait works as expected (sets deleted_at)
        // However, it doesn't set deleted_by unless we do it manually.
        $user->notifications()->update(['deleted_by' => $user->id, 'deleted_at' => now()]);

        return $this->successResponse([
            'deleted_count' => $count
        ], "{$count} notifications deleted successfully");
    }

    /**
     * Get notification statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'today' => $user->notifications()
                ->whereDate('created_at', today())
                ->count(),
            'this_week' => $user->notifications()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return $this->successResponse($stats, 'Notification statistics retrieved successfully');
    }

    /**
     * Transform notification for API response
     */
    protected function transformNotification(Notification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'data' => $data,
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
            'time_ago' => $notification->created_at->diffForHumans(),
        ];
    }
}
