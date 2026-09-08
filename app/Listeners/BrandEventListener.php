<?php

namespace App\Listeners;

use App\Events\Brand\BrandCreated;
use App\Events\Brand\BrandDeleted;
use App\Events\Brand\BrandDestroyed;
use App\Events\Brand\BrandRestored;
use App\Events\Brand\BrandStatusUpdated;
use App\Events\Brand\BrandUpdated;

class BrandEventListener
{
    /**
     * Handle Brand Created event.
     */
    public function onCreated(BrandCreated $event): void
    {
        //
    }

    /**
     * Handle Brand Updated event.
     */
    public function onUpdated(BrandUpdated $event): void
    {
        //
    }

    /**
     * Handle Brand Status Updated event.
     */
    public function onStatusUpdated(BrandStatusUpdated $event): void
    {
        //
    }

    /**
     * Handle Brand Destroyed event.
     */
    public function onDestroyed(BrandDestroyed $event): void
    {
        //
    }

    /**
     * Handle Brand Restored event.
     */
    public function onRestored(BrandRestored $event): void
    {
        //
    }

    /**
     * Handle Brand Deleted event.
     */
    public function onDeleted(BrandDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(BrandCreated::class, [self::class, 'onCreated']);
        $events->listen(BrandUpdated::class, [self::class, 'onUpdated']);
        $events->listen(BrandStatusUpdated::class, [self::class, 'onStatusUpdated']);
        $events->listen(BrandDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(BrandRestored::class, [self::class, 'onRestored']);
        $events->listen(BrandDeleted::class, [self::class, 'onDeleted']);
    }
}