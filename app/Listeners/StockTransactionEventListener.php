<?php

namespace App\Listeners;

use App\Events\StockTransaction\StockTransactionCreated;
use App\Events\StockTransaction\StockTransactionDeleted;
use App\Events\StockTransaction\StockTransactionDestroyed;
use App\Events\StockTransaction\StockTransactionRestored;
use App\Events\StockTransaction\StockTransactionStatusUpdated;
use App\Events\StockTransaction\StockTransactionUpdated;

class StockTransactionEventListener
{
    /**
     * Handle Stock Transaction Created event.
     */
    public function onCreated(StockTransactionCreated $event): void
    {
        //
    }

    /**
     * Handle Stock Transaction Updated event.
     */
    public function onUpdated(StockTransactionUpdated $event): void
    {
        //
    }

    /**
     * Handle Stock Transaction Status Updated event.
     */
    public function onStatusUpdated(StockTransactionStatusUpdated $event): void
    {
        //
    }

    /**
     * Handle Stock Transaction Destroyed event.
     */
    public function onDestroyed(StockTransactionDestroyed $event): void
    {
        //
    }

    /**
     * Handle Stock Transaction Restored event.
     */
    public function onRestored(StockTransactionRestored $event): void
    {
        //
    }

    /**
     * Handle Stock Transaction Deleted event.
     */
    public function onDeleted(StockTransactionDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(StockTransactionCreated::class, [self::class, 'onCreated']);
        $events->listen(StockTransactionUpdated::class, [self::class, 'onUpdated']);
        $events->listen(StockTransactionStatusUpdated::class, [self::class, 'onStatusUpdated']);
        $events->listen(StockTransactionDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(StockTransactionRestored::class, [self::class, 'onRestored']);
        $events->listen(StockTransactionDeleted::class, [self::class, 'onDeleted']);
    }
}
