<?php

namespace App\Listeners;

use App\Events\Project\ProjectCreated;
use App\Events\Project\ProjectDeleted;
use App\Events\Project\ProjectDestroyed;
use App\Events\Project\ProjectRestored;
use App\Events\Project\ProjectStatusUpdated;
use App\Events\Project\ProjectUpdated;

class ProjectEventListener
{
    /**
     * Handle Project Created event.
     */
    public function onCreated(ProjectCreated $event): void
    {
        //
    }

    /**
     * Handle Project Updated event.
     */
    public function onUpdated(ProjectUpdated $event): void
    {
        //
    }

    /**
     * Handle Project Status Updated event.
     */
    public function onStatusUpdated(ProjectStatusUpdated $event): void
    {
        //
    }

    /**
     * Handle Project Destroyed event.
     */
    public function onDestroyed(ProjectDestroyed $event): void
    {
        //
    }

    /**
     * Handle Project Restored event.
     */
    public function onRestored(ProjectRestored $event): void
    {
        //
    }

    /**
     * Handle Project Deleted event.
     */
    public function onDeleted(ProjectDeleted $event): void
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(ProjectCreated::class, [self::class, 'onCreated']);
        $events->listen(ProjectUpdated::class, [self::class, 'onUpdated']);
        $events->listen(ProjectStatusUpdated::class, [self::class, 'onStatusUpdated']);
        $events->listen(ProjectDestroyed::class, [self::class, 'onDestroyed']);
        $events->listen(ProjectRestored::class, [self::class, 'onRestored']);
        $events->listen(ProjectDeleted::class, [self::class, 'onDeleted']);
    }
}
