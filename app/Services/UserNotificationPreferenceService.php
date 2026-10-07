<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Collection;

/**
 * Handles each user's own on/off switch for notification channels.
 * A channel is ON for a user unless they switched it off (no saved row means ON).
 */
class UserNotificationPreferenceService
{
    /**
     * Lists the channels a user can switch (only active ones) with their current setting.
     *
     * @return Collection<int, array{id: int, name: string, description: ?string, is_enabled: bool}>
     */
    public function channelsFor(User $user): Collection
    {
        $switchedOff = UserNotificationPreference::where('user_id', $user->id)
            ->where('is_enabled', false)
            ->pluck('channel_id')
            ->all();

        return NotificationChannel::active()->ordered()
            ->whereNotIn('code', $this->alwaysOnCodes())
            ->get()
            ->map(fn (NotificationChannel $channel) => [
                'id' => $channel->id,
                'name' => $channel->name,
                'description' => $channel->description,
                'is_enabled' => ! in_array($channel->id, $switchedOff, true),
            ]);
    }

    /**
     * Saves the user's choice for one channel.
     *
     * @throws GeneralException when the channel does not exist or is switched off by the admin.
     */
    public function setPreference(User $user, int $channelId, bool $enabled): UserNotificationPreference
    {
        if (! NotificationChannel::active()->whereNotIn('code', $this->alwaysOnCodes())->whereKey($channelId)->exists()) {
            throw new GeneralException(__('This notification channel is not available.'));
        }

        return UserNotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'channel_id' => $channelId],
            ['is_enabled' => $enabled]
        );
    }

    /**
     * Says whether a user wants notifications on a channel.
     */
    public function isEnabled(User $user, int $channelId): bool
    {
        if ($this->alwaysOnChannelIds()->contains($channelId)) {
            return true;
        }

        return ! UserNotificationPreference::where('user_id', $user->id)
            ->where('channel_id', $channelId)
            ->where('is_enabled', false)
            ->exists();
    }

    /**
     * Finds which "user + channel" pairs are switched off, in one query.
     * The answer is a lookup list like ['12:3' => true] meaning user 12 switched off channel 3.
     *
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $channelIds
     * @return array<string, bool>
     */
    public function switchedOff(Collection $userIds, Collection $channelIds): array
    {
        return UserNotificationPreference::where('is_enabled', false)
            ->whereIn('user_id', $userIds)
            ->whereIn('channel_id', $channelIds)
            ->whereNotIn('channel_id', $this->alwaysOnChannelIds())
            ->get(['user_id', 'channel_id'])
            ->mapWithKeys(fn ($row) => [$row->user_id.':'.$row->channel_id => true])
            ->all();
    }

    /**
     * The codes of channels users cannot switch off (see config "notification.always_on_channels").
     *
     * @return array<int, string>
     */
    protected function alwaysOnCodes(): array
    {
        return config('notification.always_on_channels', []);
    }

    /**
     * The ids of the channels users cannot switch off.
     *
     * @return Collection<int, int>
     */
    protected function alwaysOnChannelIds(): Collection
    {
        return NotificationChannel::whereIn('code', $this->alwaysOnCodes())->pluck('id');
    }
}
