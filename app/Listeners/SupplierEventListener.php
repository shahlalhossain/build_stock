<?php

namespace App\Listeners;

use App\Events\Supplier\SupplierCreated;
use App\Events\Supplier\SupplierDeleted;
use App\Events\Supplier\SupplierDestroyed;
use App\Events\Supplier\SupplierRestored;
use App\Events\Supplier\SupplierUpdated;

class SupplierEventListener
{
    /**
     * Handle Supplier Created event.
     */
    public function onCreated(SupplierCreated $event): void
    {
        //
    }

    /**
     * Handle Supplier Updated event.
     */
    public function onUpdated(SupplierUpdated $event): void
    {
        //
    }

    /**
     * Handle Supplier Destroyed event.
     */
    public function onDestroyed(SupplierDestroyed $event): void
    {
        //
    }

    /**
     * Handle Supplier Restored event.
     */
    public function onRestored(SupplierRestored $event): void
    {
        //
    }

    /**
     * Handle Supplier Deleted event.
     */
    public function onDeleted(SupplierDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(SupplierCreated::class, [self::class, 'onCreated']);
        $events->listen(SupplierUpdated::class, [self::class, 'onUpdated']);
        $events->listen(SupplierDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(SupplierRestored::class, [self::class, 'onRestored']);
        $events->listen(SupplierDeleted::class, [self::class, 'onDeleted']);
    }
}
