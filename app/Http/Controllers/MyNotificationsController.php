<?php

namespace App\Http\Controllers;

use App\Services\MyNotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * The logged-in user's "My Notifications" board. Every action only touches the
 * user's own notifications, so no extra permission is needed.
 */
class MyNotificationsController extends Controller
{
    /**
     * Keeps the service that reads and updates the board.
     */
    public function __construct(protected MyNotificationService $board) {}

    /**
     * Shows the full board (all notifications, or only the unread ones).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $unreadOnly = $request->query('filter') === 'unread';

        return view('my-notification.index', [
            'items' => $this->board->paginate($user, $unreadOnly)->withQueryString(),
            'unreadCount' => $this->board->unreadCount($user),
            'filter' => $unreadOnly ? 'unread' : 'all',
        ]);
    }

    /**
     * Gives the header bell its numbers: unread count and the latest notifications.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $this->board->unreadCount($user),
            'items' => $this->board->latest($user, 10),
        ]);
    }

    /**
     * Marks one notification as read.
     */
    public function markRead(Request $request, int $receiver): JsonResponse
    {
        return $this->run($request, 'Notification Marked as Read.', function () use ($request, $receiver) {
            $this->board->markRead($request->user(), $receiver);
        });
    }

    /**
     * Marks all of the user's notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        return $this->run($request, 'All Notifications Marked as Read.', function () use ($request) {
            $this->board->markAllRead($request->user());
        });
    }

    /**
     * Deletes (hides) one notification from the user's board.
     */
    public function destroy(Request $request, int $receiver): JsonResponse
    {
        return $this->run($request, 'Notification Deleted.', function () use ($request, $receiver) {
            $this->board->delete($request->user(), $receiver);
        });
    }

    /**
     * Runs one board action and answers with JSON: success + the new unread count,
     * "not found" (404) for someone else's notification, or a general error (500).
     */
    protected function run(Request $request, string $successMessage, callable $action): JsonResponse
    {
        try {
            $action();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'unread_count' => $this->board->unreadCount($request->user()),
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['success' => false, 'message' => 'Notification Not Found.'], 404);
        } catch (Throwable $exception) {
            Log::error('My Notifications Action Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred. Try Again.'], 500);
        }
    }
}
