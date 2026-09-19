<?php

namespace App\Listeners;

use App\Events\Product\ProductCreated;
use App\Events\Product\ProductDeleted;
use App\Events\Product\ProductDestroyed;
use App\Events\Product\ProductRestored;
use App\Events\Product\ProductUpdated;

class ProductEventListener
{
    /**
     * Handle Product Created event.
     */
    public function onCreated(ProductCreated $event): void
    {
        //
    }

    /**
     * Handle Product Updated event.
     */
    public function onUpdated(ProductUpdated $event): void
    {
        //
    }

    /**
     * Handle Product Destroyed event.
     */
    public function onDestroyed(ProductDestroyed $event): void
    {
        //
    }

    /**
     * Handle Product Restored event.
     */
    public function onRestored(ProductRestored $event): void
    {
        //
    }

    /**
     * Handle Product Deleted event.
     */
    public function onDeleted(ProductDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(ProductCreated::class, [self::class, 'onCreated']);
        $events->listen(ProductUpdated::class, [self::class, 'onUpdated']);
        $events->listen(ProductDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(ProductRestored::class, [self::class, 'onRestored']);
        $events->listen(ProductDeleted::class, [self::class, 'onDeleted']);
    }
}
