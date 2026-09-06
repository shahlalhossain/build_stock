<?php

namespace App\Listeners;

use App\Events\Permission\PermissionCreated;
use App\Events\Permission\PermissionDeleted;
use App\Events\Permission\PermissionUpdated;

/**
 * Class PermissionEventListener.
 */
class PermissionEventListener
{
    /**
     * @param $event
     */
    public function onCreated($event)
    {
        activity('permission')
            ->performedOn($event->permission)
            ->withProperties([
                'permission' => [
                    'type' => $event->permission->type,
                    'name' => $event->permission->name,
                ],
            ])
            ->log(':causer.name created permission :subject.name with permissions: :properties.permissions');
    }

    /**
     * @param $event
     */
    public function onUpdated($event)
    {
        activity('permission')
            ->performedOn($event->permission)
            ->withProperties([
                'permission' => [
                    'type' => $event->permission->type,
                    'name' => $event->permission->name,
                ],
            ])
            ->log(':causer.name updated permission :subject.name with permissions: :properties.permissions');
    }

    /**
     * @param $event
     */
    public function onDeleted($event)
    {
        activity('permission')
            ->performedOn($event->permission)
            ->log(':causer.name deleted permission :subject.name');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(PermissionCreated::class, 'App\Listeners\PermissionEventListener@onCreated');
        $events->listen(PermissionUpdated::class, 'App\Listeners\PermissionEventListener@onUpdated');
        $events->listen(PermissionDeleted::class, 'App\Listeners\PermissionEventListener@onDeleted');
    }
}
