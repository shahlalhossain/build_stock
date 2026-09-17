<?php

namespace App\Listeners;

use App\Events\Warehouse\WarehouseCreated;
use App\Events\Warehouse\WarehouseDeleted;
use App\Events\Warehouse\WarehouseDestroyed;
use App\Events\Warehouse\WarehouseRestored;
use App\Events\Warehouse\WarehouseUpdated;

class WarehouseEventListener
{
    /**
     * Handle Warehouse Created event.
     */
    public function onCreated(WarehouseCreated $event): void
    {
        //
    }

    /**
     * Handle Warehouse Updated event.
     */
    public function onUpdated(WarehouseUpdated $event): void
    {
        //
    }

    /**
     * Handle Warehouse Destroyed event.
     */
    public function onDestroyed(WarehouseDestroyed $event): void
    {
        //
    }

    /**
     * Handle Warehouse Restored event.
     */
    public function onRestored(WarehouseRestored $event): void
    {
        //
    }

    /**
     * Handle Warehouse Deleted event.
     */
    public function onDeleted(WarehouseDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(WarehouseCreated::class, [self::class, 'onCreated']);
        $events->listen(WarehouseUpdated::class, [self::class, 'onUpdated']);
        $events->listen(WarehouseDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(WarehouseRestored::class, [self::class, 'onRestored']);
        $events->listen(WarehouseDeleted::class, [self::class, 'onDeleted']);
    }
}
