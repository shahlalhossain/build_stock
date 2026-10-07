<?php

namespace App\Services\Notification;

use App\Enums\NotificationPriority;
use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\User;
use App\Services\UserNotificationPreferenceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything the "Send Notification" screen needs: working out who will receive it,
 * showing a preview, and sending. The actual saving and queuing is done by
 * NotificationService, the same code automatic notifications use.
 */
class ManualNotificationService
{
    /**
     * Keeps the helpers this service needs.
     */
    public function __construct(
        protected NotificationService $notificationService,
        protected ReceiverResolver $receiverResolver,
        protected UserNotificationPreferenceService $preferences,
    ) {}

    /**
     * Works out the final list of user ids from the chosen users and roles.
     * Only active users are kept and nobody is listed twice.
     *
     * @param  array<int, int|string>  $userIds
     * @param  array<int, string>  $roleNames
     * @return Collection<int, int>
     */
    public function resolveUserIds(array $userIds, array $roleNames): Collection
    {
        $ids = collect($userIds)->map(fn ($id) => (int) $id)
            ->merge($this->receiverResolver->userIdsForRoles($roleNames))
            ->unique()
            ->values();

        return User::query()->whereIn('id', $ids)->where('is_active', true)->pluck('id');
    }

    /**
     * Builds the information shown on the preview screen. Nothing is saved or sent.
     *
     * @param  array<string, mixed>  $data  The validated form data.
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        $userIds = $this->resolveUserIds($data['users'] ?? [], $data['roles'] ?? []);
        $channels = $this->channelsFrom($data);

        $totalDeliveries = $userIds->count() * $channels->count();
        $switchedOff = count($this->preferences->switchedOff($userIds, $channels->pluck('id')));

        return [
            'title' => $data['title'],
            'message' => $data['message'],
            'priority' => NotificationPriority::from($data['priority'])->label(),
            'channels' => $channels->pluck('name')->all(),
            'receiver_count' => $userIds->count(),
            'delivery_count' => $totalDeliveries - $switchedOff,
            'skipped_by_preference' => $switchedOff,
            'sample_receivers' => User::whereIn('id', $userIds->take(10))->pluck('name')->all(),
            'scheduled_at' => ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at'])->format('Y-m-d H:i') : null,
        ];
    }

    /**
     * Creates and queues the manual notification.
     *
     * @param  array<string, mixed>  $data  The validated form data.
     *
     * @throws GeneralException when there is nobody to send to, or too many people.
     */
    public function send(array $data, User $sender): NotificationMessage
    {
        $userIds = $this->resolveUserIds($data['users'] ?? [], $data['roles'] ?? []);

        if ($userIds->count() > config('notification.manual.max_receivers', 10000)) {
            throw new GeneralException(__('Too many receivers. You can send to at most :max people at once.', ['max' => config('notification.manual.max_receivers', 10000)]));
        }

        $message = $this->notificationService->createManualNotification(
            $data['title'],
            $data['message'],
            $userIds->all(),
            $this->channelsFrom($data)->pluck('id')->all(),
            $sender,
            NotificationPriority::from($data['priority']),
            ! empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
        );

        if ($message === null) {
            throw new GeneralException(__('Nobody can receive this notification. The chosen users may be inactive or may have switched these channels off.'));
        }

        return $message;
    }

    /**
     * Loads the chosen channels (active ones only, in display order).
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, NotificationChannel>
     */
    protected function channelsFrom(array $data): Collection
    {
        return NotificationChannel::active()->ordered()->whereIn('id', $data['channels'] ?? [])->get();
    }
}
