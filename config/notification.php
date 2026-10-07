<?php

use App\Services\Notification\Channels\PushNotificationChannel;
use App\Services\Notification\Channels\RealtimeNotificationChannel;
use App\Services\Notification\Providers\LogPushProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Channel drivers
    |--------------------------------------------------------------------------
    | Links a channel's "driver" name (column `driver` of notification_channels)
    | to the PHP class that sends it. The class must implement
    | App\Services\Notification\Channels\NotificationChannelInterface.
    */
    'drivers' => [
        'push' => PushNotificationChannel::class,
        'realtime' => RealtimeNotificationChannel::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk size
    |--------------------------------------------------------------------------
    | How many receivers we create in one go. Keeps memory low for big lists.
    */
    'chunk_size' => 500,

    /*
    |--------------------------------------------------------------------------
    | "My Notifications" board
    |--------------------------------------------------------------------------
    | board_channel: code of the channel whose deliveries are shown on every
    |                user's "My Notifications" board and in the header bell.
    | always_on_channels: channel codes users cannot switch off in their
    |                preferences (the board is their inbox, so it is always on).
    */
    'board_channel' => 'realtime',
    'always_on_channels' => ['realtime'],

    /*
    |--------------------------------------------------------------------------
    | Retry policy
    |--------------------------------------------------------------------------
    | max_attempts: how many times we try one delivery before giving up.
    | backoff: seconds to wait before the 2nd, 3rd... attempt (the last value
    |          is reused if there are more attempts than values).
    | Errors a provider marks as permanent (for example an invalid device) are never retried.
    */
    'retry' => [
        'max_attempts' => 3,
        'backoff' => [60, 300],
    ],

    /*
    |--------------------------------------------------------------------------
    | Manual notifications
    |--------------------------------------------------------------------------
    | max_receivers: the most people one manual notification may be sent to.
    */
    'manual' => [
        'max_receivers' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Push provider
    |--------------------------------------------------------------------------
    | Which service really delivers push messages. "log" only writes the
    | message to the application log (safe for development and tests).
    | Set NOTIFICATION_PUSH_PROVIDER in .env to change it. Add a new provider
    | by creating a class that implements PushProvider and listing it here.
    */
    'push' => [
        'provider' => env('NOTIFICATION_PUSH_PROVIDER', 'log'),
        'providers' => [
            'log' => LogPushProvider::class,
        ],
    ],

];
