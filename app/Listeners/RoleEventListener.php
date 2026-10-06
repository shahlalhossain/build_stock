<?php

namespace App\Listeners;

use App\Events\Role\RoleCreated;
use App\Events\Role\RoleDeleted;
use App\Events\Role\RoleUpdated;
use Illuminate\Events\Dispatcher;

/**
 * Class RoleEventListener.
 */
class RoleEventListener
{
    public function onCreated($event)
    {
        activity('role')
            ->performedOn($event->role)
            ->withProperties([
                'role' => [
                    'type' => $event->role->type,
                    'name' => $event->role->name,
                ],
                'permissions' => $event->role->permissions->count() ? $event->role->permissions->pluck('description')->implode(', ') : 'None',
            ])
            ->log(':causer.name created role :subject.name with permissions: :properties.permissions');
    }

    public function onUpdated($event)
    {
        activity('role')
            ->performedOn($event->role)
            ->withProperties([
                'role' => [
                    'type' => $event->role->type,
                    'name' => $event->role->name,
                ],
                'permissions' => $event->role->permissions->count() ? $event->role->permissions->pluck('description')->implode(', ') : 'None',
            ])
            ->log(':causer.name updated role :subject.name with permissions: :properties.permissions');
    }

    public function onDeleted($event)
    {
        activity('role')
            ->performedOn($event->role)
            ->log(':causer.name deleted role :subject.name');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(RoleCreated::class, 'App\Listeners\RoleEventListener@onCreated');
        $events->listen(RoleUpdated::class, 'App\Listeners\RoleEventListener@onUpdated');
        $events->listen(RoleDeleted::class, 'App\Listeners\RoleEventListener@onDeleted');
    }
}
