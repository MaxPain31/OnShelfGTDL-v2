<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $limit = $request->input('limit', 10);

        $notifications = $user->notifications()
            ->limit($limit)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_full' => $notification->created_at->format('M d, Y h:i A'),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        if (!$user || $notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->markAsRead();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function showAll(Request $request): View
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $notificationsQuery = $user->notifications();

        // Filter by read/unread status
        if ($request->filled('status')) {
            if ($request->input('status') === 'unread') {
                $notificationsQuery->whereNull('read_at');
            } elseif ($request->input('status') === 'read') {
                $notificationsQuery->whereNotNull('read_at');
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $notificationsQuery->where('type', $request->input('type'));
        }

        $notifications = $notificationsQuery->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        // Get notification types for filter
        $notificationTypes = Notification::where('user_id', $user->id)
            ->distinct()
            ->pluck('type')
            ->toArray();

        // Add book image information to notifications that have book_id in data
        $notifications->getCollection()->transform(function ($notification) {
            if ($notification->data && isset($notification->data['book_id'])) {
                $book = Book::find($notification->data['book_id']);
                if ($book) {
                    // Get the data array, modify it, and set it back
                    $data = $notification->data;
                    $data['book_image_path'] = $book->image_path
                        ? asset('storage/' . $book->image_path)
                        : null;
                    $data['book_image_alt'] = $book->book_name;
                    $notification->data = $data;
                }
            }
            return $notification;
        });

        // Get the notification ID from query parameter if present
        $selectedNotificationId = $request->input('notification');
        $selectedNotification = null;

        if ($selectedNotificationId) {
            $selectedNotification = $notifications->getCollection()->firstWhere('id', $selectedNotificationId);
            // If not found in current page, fetch it separately
            if (!$selectedNotification) {
                $selectedNotification = Notification::where('id', $selectedNotificationId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($selectedNotification && $selectedNotification->data && isset($selectedNotification->data['book_id'])) {
                    $book = Book::find($selectedNotification->data['book_id']);
                    if ($book) {
                        $data = $selectedNotification->data;
                        $data['book_image_path'] = $book->image_path
                            ? asset('storage/' . $book->image_path)
                            : null;
                        $data['book_image_alt'] = $book->book_name;
                        $selectedNotification->data = $data;
                    }
                }
            }
        }

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'notificationTypes' => $notificationTypes,
            'selectedStatus' => $request->input('status', ''),
            'selectedType' => $request->input('type', ''),
            'selectedNotificationId' => $selectedNotificationId,
            'selectedNotification' => $selectedNotification,
        ]);
    }
}
