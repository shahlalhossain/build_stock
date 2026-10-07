<?php

namespace App\Services;

use App\Enums\NotificationReceiverStatus;
use App\Models\NotificationChannel;
use App\Models\NotificationReceiver;
use App\Models\User;
use App\Services\Notification\TemplateRenderer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The logged-in user's "My Notifications" board.
 * The board is simply the user's own deliveries on the board channel (see config
 * "notification.board_channel"). Reading and deleting only update two columns on that
 * delivery row: read_at and user_deleted_at. Nothing is ever removed from the database.
 */
class MyNotificationService
{
    /**
     * Keeps the helper that writes the final text of a notification.
     */
    public function __construct(protected TemplateRenderer $renderer) {}

    /**
     * Lists the user's notifications, newest first (a page at a time).
     * Each item is a ready-to-show array (see toItem()).
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginate(User $user, bool $unreadOnly = false, int $perPage = 15): LengthAwarePaginator
    {
        return $this->boardQuery($user)
            ->when($unreadOnly, fn (Builder $query) => $query->whereNull('read_at'))
            ->with('message')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(fn (NotificationReceiver $receiver) => $this->toItem($receiver));
    }

    /**
     * The newest notifications (used by the header bell).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function latest(User $user, int $limit = 10): Collection
    {
        return $this->boardQuery($user)
            ->with('message')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (NotificationReceiver $receiver) => $this->toItem($receiver));
    }

    /**
     * How many notifications the user has not read yet.
     */
    public function unreadCount(User $user): int
    {
        return $this->boardQuery($user)->whereNull('read_at')->count();
    }

    /**
     * Marks one notification as read (does nothing if it was already read).
     *
     * @throws ModelNotFoundException when it is not one of this user's notifications.
     */
    public function markRead(User $user, int $receiverId): NotificationReceiver
    {
        $receiver = $this->boardQuery($user)->findOrFail($receiverId);

        if ($receiver->read_at === null) {
            $receiver->update(['read_at' => now()]);
        }

        return $receiver;
    }

    /**
     * Marks every unread notification of the user as read. Returns how many were changed.
     */
    public function markAllRead(User $user): int
    {
        return $this->boardQuery($user)->whereNull('read_at')->update(['read_at' => now()]);
    }

    /**
     * "Deletes" one notification from the user's board. The row stays in the
     * database (admins still see it in delivery history); it is only hidden from this user.
     *
     * @throws ModelNotFoundException when it is not one of this user's notifications.
     */
    public function delete(User $user, int $receiverId): NotificationReceiver
    {
        $receiver = $this->boardQuery($user)->findOrFail($receiverId);

        $receiver->update(['user_deleted_at' => now()]);

        return $receiver;
    }

    /**
     * Turns one delivery into the plain array the screens and the real-time pop-up use.
     *
     * @return array<string, mixed>
     */
    public function toItem(NotificationReceiver $receiver): array
    {
        $message = $receiver->message;
        $content = $this->renderer->renderForReceiver($receiver);
        $when = $receiver->sent_at ?? $receiver->created_at;

        return [
            'id' => $receiver->id,
            'title' => $content['title'] !== '' ? $content['title'] : (string) $message->title,
            'body' => $content['body'],
            'url' => $this->safeUrl($message->data['url'] ?? null),
            'priority' => $message->priority->value,
            'is_read' => $receiver->read_at !== null,
            'time_human' => $when->diffForHumans(),
            'created_at' => $when->toIso8601String(),
        ];
    }

    /**
     * The query for what is on a user's board: their own deliveries on the board
     * channel that were sent and that they did not delete.
     *
     * @return Builder<NotificationReceiver>
     */
    protected function boardQuery(User $user): Builder
    {
        return NotificationReceiver::query()
            ->where('user_id', $user->id)
            ->whereIn('channel_id', NotificationChannel::where('code', config('notification.board_channel'))->select('id'))
            ->whereIn('status', [NotificationReceiverStatus::Sent->value, NotificationReceiverStatus::Delivered->value])
            ->whereNull('user_deleted_at');
    }

    /**
     * Only lets normal links through (a page of this site, or http/https). Anything else
     * (for example "javascript:...") is dropped.
     */
    protected function safeUrl(mixed $url): ?string
    {
        return is_string($url) && Str::startsWith($url, ['/', 'http://', 'https://']) ? $url : null;
    }
}
