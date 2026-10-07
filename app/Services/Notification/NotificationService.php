<?php

namespace App\Services\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Jobs\Notifications\SendNotificationJob;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\UserNotificationPreferenceService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The one place that creates notifications.
 * Business modules never send anything themselves; they fire an event, and
 * the listener calls this service.
 *
 * This service only SAVES the notification and its receivers.
 * Sending (queue, channels, providers) is done later by SendNotificationJob.
 */
class NotificationService
{
    /**
     * Keeps the helpers this service needs.
     */
    public function __construct(
        protected ReceiverResolver $receiverResolver,
        protected TemplateRenderer $templateRenderer,
        protected UserNotificationPreferenceService $preferences,
    ) {}

    /**
     * Creates the notification for a business event such as "brand.created".
     * Returns null when nothing needs to be sent (no active setting, no usable
     * channel, or nobody to notify).
     *
     * @param  array<string, mixed>  $data  Values for the template placeholders and extra details.
     */
    public function createAutomaticNotification(string $eventCode, array $data = [], ?User $actor = null): ?NotificationMessage
    {
        $setting = $this->resolveNotificationSetting($eventCode);
        if ($setting === null) {
            return null;
        }

        $channels = $this->resolveChannels($setting);
        if ($channels->isEmpty()) {
            return null;
        }

        $users = $this->resolveReceivers($setting);
        if (! $users->exists()) {
            return null;
        }

        $data = $this->buildData($data, $actor);
        $content = $this->renderContent($setting, $channels->first(), $data);

        return $this->saveAndDispatch([
            'notification_setting_id' => $setting->id,
            'type' => NotificationType::Automatic,
            'event_code' => $eventCode,
            'title' => $content['title'],
            'message_body' => $content['body'],
            'data' => $data,
            'created_by' => $actor?->id,
        ], $users, $channels);
    }

    /**
     * Creates a notification written by a person (manual notification).
     * It uses the same tables and the same steps as an automatic one.
     * The caller must already have checked that the person is allowed to send.
     *
     * @param  array<int, int>  $userIds  Who should receive it.
     * @param  array<int, int>  $channelIds  Which channels to use.
     */
    public function createManualNotification(
        string $title,
        string $body,
        array $userIds,
        array $channelIds,
        ?User $creator = null,
        NotificationPriority $priority = NotificationPriority::Normal,
        ?CarbonInterface $scheduledAt = null,
    ): ?NotificationMessage {
        $channels = NotificationChannel::active()->ordered()->whereIn('id', $channelIds)->get();
        $users = User::query()->whereIn('id', $userIds)->where('is_active', true);

        if ($channels->isEmpty() || ! $users->exists()) {
            return null;
        }

        return $this->saveAndDispatch([
            'type' => NotificationType::Manual,
            'title' => $title,
            'message_body' => $body,
            'data' => $this->buildData([], $creator),
            'priority' => $priority,
            'scheduled_at' => $scheduledAt,
            'created_by' => $creator?->id,
        ], $users, $channels);
    }

    /**
     * Finds the active notification setting for an event code (or null).
     */
    public function resolveNotificationSetting(string $eventCode): ?NotificationSetting
    {
        return NotificationSetting::active()->forEvent($eventCode)->first();
    }

    /**
     * Returns the channels we can really use for a setting: the channel is on,
     * it is on for this setting, AND the setting has an active template for it.
     *
     * @return Collection<int, NotificationChannel>
     */
    public function resolveChannels(NotificationSetting $setting): Collection
    {
        $channelIdsWithTemplate = $setting->templates()->active()->pluck('channel_id');

        return $setting->activeChannels()
            ->whereIn('notification_channels.id', $channelIdsWithTemplate)
            ->orderBy('notification_channels.sort_order')
            ->get();
    }

    /**
     * Returns a query for the users who should receive this setting's notifications.
     *
     * @return Builder<User>
     */
    public function resolveReceivers(NotificationSetting $setting): Builder
    {
        return $this->receiverResolver->resolve($setting);
    }

    /**
     * Saves the notification row itself.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createNotification(array $attributes): NotificationMessage
    {
        return NotificationMessage::create($attributes + [
            'priority' => NotificationPriority::Normal,
            'status' => NotificationStatus::Pending,
            'is_active' => true,
        ]);
    }

    /**
     * Saves one receiver row for every user + channel pair,
     * except where the user switched that channel off for themselves.
     * Users are read in small chunks so a list of 10,000 users is safe.
     * Returns how many receiver rows were created.
     *
     * @param  Builder<User>  $users
     * @param  Collection<int, NotificationChannel>  $channels
     */
    public function createReceivers(NotificationMessage $message, Builder $users, Collection $channels): int
    {
        $created = 0;

        $users->chunkById(config('notification.chunk_size', 500), function ($userChunk) use ($message, $channels, &$created) {
            $now = now();
            $rows = [];
            $switchedOff = $this->preferences->switchedOff($userChunk->pluck('id'), $channels->pluck('id'));

            foreach ($userChunk as $user) {
                foreach ($channels as $channel) {
                    if (isset($switchedOff[$user->id.':'.$channel->id])) {
                        continue;
                    }

                    $rows[] = [
                        'notification_message_id' => $message->id,
                        'user_id' => $user->id,
                        'channel_id' => $channel->id,
                        'status' => NotificationReceiverStatus::Pending->value,
                        'attempts' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            NotificationReceiver::insert($rows);
            $created += count($rows);
        });

        return $created;
    }

    /**
     * Saves the notification and its receivers in ONE database transaction, then
     * queues the deliveries. If every user switched the channels off (so there is
     * nobody left to send to), nothing is kept and null is returned.
     *
     * @param  array<string, mixed>  $attributes
     * @param  Builder<User>  $users
     * @param  Collection<int, NotificationChannel>  $channels
     */
    protected function saveAndDispatch(array $attributes, Builder $users, Collection $channels): ?NotificationMessage
    {
        $message = DB::transaction(function () use ($attributes, $users, $channels) {
            $message = $this->createNotification($attributes);

            if ($this->createReceivers($message, $users, $channels) === 0) {
                $message->delete();

                return null;
            }

            return $message;
        });

        if ($message !== null) {
            $this->dispatchJobs($message);
        }

        return $message;
    }

    /**
     * Puts every pending delivery of a notification into the queue, one small
     * chunk at a time (safe for thousands of receivers).
     * Each job runs only after the database transaction is saved.
     * A notification with a future "scheduled_at" is delayed until that time.
     * Returns how many deliveries were queued.
     */
    public function dispatchJobs(NotificationMessage $message): int
    {
        $message->update(['status' => NotificationStatus::Processing]);

        $delay = $message->scheduled_at?->isFuture() ? $message->scheduled_at : null;
        $queued = 0;

        $message->receivers()
            ->withStatus(NotificationReceiverStatus::Pending)
            ->chunkById(config('notification.chunk_size', 500), function ($receivers) use ($delay, &$queued) {
                NotificationReceiver::whereIn('id', $receivers->pluck('id'))->update([
                    'status' => NotificationReceiverStatus::Queued->value,
                    'queued_at' => now(),
                ]);

                foreach ($receivers as $receiver) {
                    $receiver->addLog('queued', NotificationReceiverStatus::Queued->value);

                    SendNotificationJob::dispatch($receiver->id)->delay($delay);
                    $queued++;
                }
            });

        return $queued;
    }

    /**
     * Works out the overall status of a notification from the status of all its deliveries
     * and saves it:
     *  - some deliveries not finished yet  -> processing
     *  - all delivered/sent                -> completed
     *  - all failed                        -> failed
     *  - a mix of ok and failed            -> partial
     *  - all cancelled                     -> cancelled
     */
    public function refreshStatus(NotificationMessage $message): NotificationStatus
    {
        $counts = $message->receivers()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (NotificationReceiverStatus ...$statuses) => collect($statuses)->sum(fn ($status) => (int) ($counts[$status->value] ?? 0));

        $unfinished = $count(NotificationReceiverStatus::Pending, NotificationReceiverStatus::Queued, NotificationReceiverStatus::Processing);
        $ok = $count(NotificationReceiverStatus::Sent, NotificationReceiverStatus::Delivered);
        $failed = $count(NotificationReceiverStatus::Failed);

        $status = match (true) {
            $unfinished > 0 => NotificationStatus::Processing,
            $ok > 0 && $failed > 0 => NotificationStatus::Partial,
            $ok > 0 => NotificationStatus::Completed,
            $failed > 0 => NotificationStatus::Failed,
            default => NotificationStatus::Cancelled,
        };

        $message->update(['status' => $status]);

        return $status;
    }

    /**
     * Adds the standard placeholder values (actor name, email, time) to the event data.
     * Values given by the event are kept and win over the standard ones.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildData(array $data, ?User $actor): array
    {
        return $data + [
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'actor_email' => $actor?->email,
            'created_at' => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Writes the notification's title and body using the template of the
     * first channel. (Each channel writes its own final text when it sends.)
     *
     * @param  array<string, mixed>  $data
     * @return array{title: ?string, body: string}
     */
    protected function renderContent(NotificationSetting $setting, NotificationChannel $channel, array $data): array
    {
        $template = $setting->templates()->active()->where('channel_id', $channel->id)->first();
        $rendered = $this->templateRenderer->renderTemplate($template, $data);

        return [
            'title' => $rendered['title'] !== '' ? $rendered['title'] : $setting->name,
            'body' => $rendered['body'],
        ];
    }
}
