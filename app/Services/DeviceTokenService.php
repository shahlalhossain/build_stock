<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDeviceToken;

/**
 * Saves and removes the device addresses ("tokens") that push notifications are sent to.
 */
class DeviceTokenService
{
    /**
     * Saves a device token for a user and switches it on.
     * If the same token already exists (even for another user, for example on a
     * shared browser) it is moved to this user.
     */
    public function register(User $user, string $token, string $platform = 'web', ?string $deviceName = null): UserDeviceToken
    {
        return UserDeviceToken::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $platform,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Removes one of the user's own device tokens. Returns true if one was removed.
     */
    public function unregister(User $user, string $token): bool
    {
        return UserDeviceToken::where('user_id', $user->id)->where('token', $token)->delete() > 0;
    }
}
