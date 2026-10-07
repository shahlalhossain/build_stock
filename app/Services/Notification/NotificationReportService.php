<?php

namespace App\Services\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;

/**
 * Numbers for the notification dashboard. Everything is counted from the
 * database for a time period (the last 30 days by default). Nothing is stored.
 */
class NotificationReportService
{
    /**
     * Builds the dashboard numbers for the last $days days.
     *
     * Derived value: "success rate" = (sent + delivered) / (sent + delivered + failed) x 100.
     * Deliveries that are still waiting or were cancelled are not part of the rate.
     *
     * @return array<string, mixed>
     */
    public function summary(int $days = 30): array
    {
        $since = now()->subDays($days)->startOfDay();

        $notificationCounts = NotificationMessage::where('created_at', '>=', $since)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $deliveryCounts = NotificationReceiver::where('created_at', '>=', $since)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $succeeded = $this->sum($deliveryCounts, [NotificationReceiverStatus::Sent, NotificationReceiverStatus::Delivered]);
        $failed = $this->sum($deliveryCounts, [NotificationReceiverStatus::Failed]);

        return [
            'days' => $days,
            'since' => $since,
            'notifications' => [
                'total' => $notificationCounts->sum(),
                'completed' => $this->sum($notificationCounts, [NotificationStatus::Completed]),
                'partial' => $this->sum($notificationCounts, [NotificationStatus::Partial]),
                'failed' => $this->sum($notificationCounts, [NotificationStatus::Failed]),
                'in_progress' => $this->sum($notificationCounts, [NotificationStatus::Pending, NotificationStatus::Processing]),
            ],
            'deliveries' => [
                'total' => $deliveryCounts->sum(),
                'succeeded' => $succeeded,
                'failed' => $failed,
                'waiting' => $this->sum($deliveryCounts, [NotificationReceiverStatus::Pending, NotificationReceiverStatus::Queued, NotificationReceiverStatus::Processing]),
                'cancelled' => $this->sum($deliveryCounts, [NotificationReceiverStatus::Cancelled]),
                'success_rate' => ($succeeded + $failed) > 0 ? round($succeeded / ($succeeded + $failed) * 100, 1) : null,
            ],
        ];
    }

    /**
     * Adds up the counts of the given statuses.
     *
     * @param  iterable<string, int>  $counts  Totals keyed by status word.
     * @param  array<int, NotificationStatus|NotificationReceiverStatus>  $statuses
     */
    protected function sum(iterable $counts, array $statuses): int
    {
        $total = 0;

        foreach ($statuses as $status) {
            $total += (int) ($counts[$status->value] ?? 0);
        }

        return $total;
    }
}
