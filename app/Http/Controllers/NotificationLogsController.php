<?php

namespace App\Http\Controllers;

use App\DataTables\NotificationLogsDataTable;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * The technical history of deliveries (queued, sent, failed, retry...), for troubleshooting.
 * Protected by the notification-log.* permissions (see routes/web.php).
 */
class NotificationLogsController extends Controller
{
    /**
     * The log steps a delivery can have (used for the "Event" filter).
     *
     * @var array<int, string>
     */
    public const EVENTS = ['queued', 'processing', 'provider_response', 'sent', 'delivered', 'retry', 'failed', 'skipped'];

    /**
     * Shows the log list with its filters.
     */
    public function index(NotificationLogsDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('notification-log.index', [
            'events' => self::EVENTS,
            'channels' => NotificationChannel::ordered()->get(['id', 'name']),
        ]);
    }

    /**
     * Shows one log line with its full request and response details.
     */
    public function show(NotificationLog $notificationLog): View
    {
        $data['log'] = $notificationLog->load(['receiver.user', 'receiver.channel']);

        return view('notification-log.show', $data);
    }
}
