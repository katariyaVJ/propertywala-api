<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * List user notifications with tab filtering
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Notification::where('user_id', $user->id);

        if ($request->filled('category') && $request->query('category') !== 'All') {
            $cat = strtolower($request->query('category'));
            $query->where('category', 'LIKE', "%{$cat}%");
        }

        $notifications = $query->latest()->paginate(25);

        return $this->successResponse(
            $notifications->items(),
            'Notifications retrieved successfully',
            200,
            [
                'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
                'total' => $notifications->total(),
            ]
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);

        return $this->successResponse(null, 'All notifications marked as read');
    }

    /**
     * Clear all notifications
     */
    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->delete();

        return $this->successResponse(null, 'All notifications cleared successfully');
    }
}

