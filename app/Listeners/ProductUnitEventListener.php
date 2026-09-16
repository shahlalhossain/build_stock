<?php

namespace App\Listeners;

use App\Events\ProductUnit\ProductUnitCreated;
use App\Events\ProductUnit\ProductUnitDeleted;
use App\Events\ProductUnit\ProductUnitDestroyed;
use App\Events\ProductUnit\ProductUnitRestored;
use App\Events\ProductUnit\ProductUnitUpdated;

class ProductUnitEventListener
{
    /**
     * Handle ProductUnit Created event.
     */
    public function onCreated(ProductUnitCreated $event): void
    {
        //
    }

    /**
     * Handle ProductUnit Updated event.
     */
    public function onUpdated(ProductUnitUpdated $event): void
    {
        //
    }

    /**
     * Handle ProductUnit Destroyed event.
     */
    public function onDestroyed(ProductUnitDestroyed $event): void
    {
        //
    }

    /**
     * Handle ProductUnit Restored event.
     */
    public function onRestored(ProductUnitRestored $event): void
    {
        //
    }

    /**
     * Handle ProductUnit Deleted event.
     */
    public function onDeleted(ProductUnitDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(ProductUnitCreated::class, [self::class, 'onCreated']);
        $events->listen(ProductUnitUpdated::class, [self::class, 'onUpdated']);
        $events->listen(ProductUnitDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(ProductUnitRestored::class, [self::class, 'onRestored']);
        $events->listen(ProductUnitDeleted::class, [self::class, 'onDeleted']);
    }
}
