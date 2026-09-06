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
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                ],
            ])
            ->log(':causer.name created brand :subject.name');
    }

    /**
     * Handle Brand Updated event.
     */
    public function onUpdated(BrandUpdated $event): void
    {
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                ],
            ])
            ->log(':causer.name updated brand :subject.name');
    }

    /**
     * Handle Brand Status Updated event.
     */
    public function onStatusUpdated(BrandStatusUpdated $event): void
    {
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                    'old_status' => $event->oldStatus,
                    'new_status' => $event->newStatus,
                ],
            ])
            ->log(':causer.name changed status of brand :subject.name');
    }

    /**
     * Handle Brand Destroyed event.
     */
    public function onDestroyed(BrandDestroyed $event): void
    {
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                ],
            ])
            ->log(':causer.name permanently destroyed brand :subject.name');
    }

    /**
     * Handle Brand Restored event.
     */
    public function onRestored(BrandRestored $event): void
    {
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                ],
            ])
            ->log(':causer.name restored brand :subject.name');
    }

    /**
     * Handle Brand Deleted event.
     */
    public function onDeleted(BrandDeleted $event): void
    {
        activity('brand')
            ->performedOn($event->brand)
            ->withProperties([
                'brand' => [
                    'name' => $event->brand->name,
                ],
            ])
            ->log(':causer.name deleted brand :subject.name');
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(
            BrandCreated::class,
            [self::class, 'onCreated']
        );

        $events->listen(
            BrandUpdated::class,
            [self::class, 'onUpdated']
        );

        $events->listen(
            BrandDeleted::class,
            [self::class, 'onDeleted']
        );

        $events->listen(
            BrandDestroyed::class,
            [self::class, 'onDestroyed']
        );

        $events->listen(
            BrandRestored::class,
            [self::class, 'onRestored']
        );

        $events->listen(
            BrandStatusUpdated::class,
            [self::class, 'onStatusUpdated']
        );
    }
}