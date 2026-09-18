<?php

namespace App\Listeners;

use App\Events\Attribute\AttributeCreated;
use App\Events\Attribute\AttributeDeleted;
use App\Events\Attribute\AttributeDestroyed;
use App\Events\Attribute\AttributeRestored;
use App\Events\Attribute\AttributeUpdated;

class AttributeEventListener
{
    /**
     * Handle Attribute Created event.
     */
    public function onCreated(AttributeCreated $event): void
    {
        //
    }

    /**
     * Handle Attribute Updated event.
     */
    public function onUpdated(AttributeUpdated $event): void
    {
        //
    }

    /**
     * Handle Attribute Destroyed event.
     */
    public function onDestroyed(AttributeDestroyed $event): void
    {
        //
    }

    /**
     * Handle Attribute Restored event.
     */
    public function onRestored(AttributeRestored $event): void
    {
        //
    }

    /**
     * Handle Attribute Deleted event.
     */
    public function onDeleted(AttributeDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(AttributeCreated::class, [self::class, 'onCreated']);
        $events->listen(AttributeUpdated::class, [self::class, 'onUpdated']);
        $events->listen(AttributeDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(AttributeRestored::class, [self::class, 'onRestored']);
        $events->listen(AttributeDeleted::class, [self::class, 'onDeleted']);
    }
}
