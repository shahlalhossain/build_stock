<?php

namespace App\Http\Controllers;

use App\DataTables\NotificationsDataTable;
use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Exceptions\GeneralException;
use App\Http\Requests\Notification\SendManualNotificationRequest;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use App\Models\NotificationMessage;
use App\Models\Role;
use App\Models\User;
use App\Services\Notification\ManualNotificationService;
use App\Services\Notification\NotificationReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Manual notifications: the "Send Notification" screen and the notification details page.
 * Every route is protected by a permission (see routes/web.php).
 */
class NotificationsController extends Controller
{
    /**
     * Keeps the service that prepares and sends manual notifications.
     */
    public function __construct(protected ManualNotificationService $manualNotificationService) {}

    /**
     * Shows the notification list with the dashboard numbers and filters.
     */
    public function index(NotificationsDataTable $dataTable, NotificationReportService $reports): JsonResponse|View
    {
        return $dataTable->render('notification.index', [
            'summary' => $reports->summary(30),
            'statuses' => NotificationStatus::cases(),
            'types' => NotificationType::cases(),
        ]);
    }

    /**
     * Shows the empty "Send Notification" form.
     */
    public function create(): View
    {
        $data['channels'] = NotificationChannel::active()->ordered()->get();
        $data['roles'] = Role::orderBy('name')->get(['id', 'name']);
        $data['priorities'] = NotificationPriority::cases();
        $data['selectedUsers'] = User::whereIn('id', old('users', []))->get(['id', 'name', 'email']);

        return view('notification.create', $data);
    }

    /**
     * Searches active users by name, email or username (for the receiver drop-down).
     */
    public function receiverSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $users = User::query()
            ->where('is_active', true)
            ->when($term !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'results' => $users->map(fn (User $user) => ['id' => $user->id, 'text' => $user->name.' ('.$user->email.')']),
        ]);
    }

    /**
     * Shows what would be sent and to how many people. Nothing is saved or sent.
     */
    public function preview(SendManualNotificationRequest $request): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'preview' => $this->manualNotificationService->preview($request->validated())]);
        } catch (Throwable $exception) {
            Log::error('Manual Notification Preview Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Building the Preview.'], 500);
        }
    }

    /**
     * Creates the manual notification and queues it, then opens its details page.
     */
    public function store(SendManualNotificationRequest $request): RedirectResponse
    {
        try {
            $message = $this->manualNotificationService->send($request->validated(), $request->user());

            return redirect()->route('notification.show', $message->id)->with('success', 'Notification Queued for Sending.');
        } catch (GeneralException $exception) {
            Log::error('Manual Notification Failed: '.$exception->getMessage());

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Sending Manual Notification: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Shows one notification: its message, overall status, deliveries and history.
     * Only the first 100 deliveries and the last 50 history lines are listed.
     */
    public function show(NotificationMessage $notificationMessage): View
    {
        $data['notification'] = $notificationMessage->load(['creator', 'setting']);
        $data['statusCounts'] = $notificationMessage->receivers()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $data['receivers'] = $notificationMessage->receivers()->with(['user', 'channel'])->orderBy('id')->limit(100)->get();
        $data['receiverTotal'] = $notificationMessage->receivers()->count();
        $data['logs'] = NotificationLog::whereHas('receiver', fn ($query) => $query->where('notification_message_id', $notificationMessage->id))
            ->with('receiver.user', 'receiver.channel')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('notification.show', $data);
    }
}
