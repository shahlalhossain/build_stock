<?php

namespace App\Listeners;

use App\Events\Category\CategoryCreated;
use App\Events\Category\CategoryDeleted;
use App\Events\Category\CategoryDestroyed;
use App\Events\Category\CategoryRestored;
use App\Events\Category\CategoryUpdated;

class CategoryEventListener
{
    /**
     * Handle Category Created event.
     */
    public function onCreated(CategoryCreated $event): void
    {
        //
    }

    /**
     * Handle Category Updated event.
     */
    public function onUpdated(CategoryUpdated $event): void
    {
        //
    }

    /**
     * Handle Category Destroyed event.
     */
    public function onDestroyed(CategoryDestroyed $event): void
    {
        //
    }

    /**
     * Handle Category Restored event.
     */
    public function onRestored(CategoryRestored $event): void
    {
        //
    }

    /**
     * Handle Category Deleted event.
     */
    public function onDeleted(CategoryDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(CategoryCreated::class, [self::class, 'onCreated']);
        $events->listen(CategoryUpdated::class, [self::class, 'onUpdated']);
        $events->listen(CategoryDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(CategoryRestored::class, [self::class, 'onRestored']);
        $events->listen(CategoryDeleted::class, [self::class, 'onDeleted']);
    }
}
