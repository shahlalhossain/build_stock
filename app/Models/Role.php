<?php

namespace App\Models;

use App\Models\Traits\Relationship\RoleRelationship;
use App\Models\Traits\Attribute\RoleAttribute;
use App\Models\Traits\Method\RoleMethod;
use App\Models\Traits\Scope\RoleScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use RoleRelationship,
        RoleAttribute,
        RoleMethod,
        RoleScope,
        SoftDeletes,
        LogsActivity;

    protected $fillable = ['type', 'guard_name', 'name', 'description', 'active', 'created_by', 'updated_by'];

    protected static array $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'guard_name', 'parent_id', 'name', 'description'])
            ->useLogName('Role')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Record has been $eventName");
    }
}
