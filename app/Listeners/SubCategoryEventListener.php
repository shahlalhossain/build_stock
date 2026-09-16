<?php

namespace App\Listeners;

use App\Events\SubCategory\SubCategoryCreated;
use App\Events\SubCategory\SubCategoryDeleted;
use App\Events\SubCategory\SubCategoryDestroyed;
use App\Events\SubCategory\SubCategoryRestored;
use App\Events\SubCategory\SubCategoryUpdated;

class SubCategoryEventListener
{
    /**
     * Handle SubCategory Created event.
     */
    public function onCreated(SubCategoryCreated $event): void
    {
        //
    }

    /**
     * Handle SubCategory Updated event.
     */
    public function onUpdated(SubCategoryUpdated $event): void
    {
        //
    }

    /**
     * Handle SubCategory Destroyed event.
     */
    public function onDestroyed(SubCategoryDestroyed $event): void
    {
        //
    }

    /**
     * Handle SubCategory Restored event.
     */
    public function onRestored(SubCategoryRestored $event): void
    {
        //
    }

    /**
     * Handle SubCategory Deleted event.
     */
    public function onDeleted(SubCategoryDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(SubCategoryCreated::class, [self::class, 'onCreated']);
        $events->listen(SubCategoryUpdated::class, [self::class, 'onUpdated']);
        $events->listen(SubCategoryDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(SubCategoryRestored::class, [self::class, 'onRestored']);
        $events->listen(SubCategoryDeleted::class, [self::class, 'onDeleted']);
    }
}
