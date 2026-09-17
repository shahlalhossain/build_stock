<?php

namespace App\Listeners;

use App\Events\Store\StoreCreated;
use App\Events\Store\StoreDeleted;
use App\Events\Store\StoreDestroyed;
use App\Events\Store\StoreRestored;
use App\Events\Store\StoreUpdated;

class StoreEventListener
{
    /**
     * Handle Store Created event.
     */
    public function onCreated(StoreCreated $event): void
    {
        //
    }

    /**
     * Handle Store Updated event.
     */
    public function onUpdated(StoreUpdated $event): void
    {
        //
    }

    /**
     * Handle Store Destroyed event.
     */
    public function onDestroyed(StoreDestroyed $event): void
    {
        //
    }

    /**
     * Handle Store Restored event.
     */
    public function onRestored(StoreRestored $event): void
    {
        //
    }

    /**
     * Handle Store Deleted event.
     */
    public function onDeleted(StoreDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(StoreCreated::class, [self::class, 'onCreated']);
        $events->listen(StoreUpdated::class, [self::class, 'onUpdated']);
        $events->listen(StoreDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(StoreRestored::class, [self::class, 'onRestored']);
        $events->listen(StoreDeleted::class, [self::class, 'onDeleted']);
    }
}
