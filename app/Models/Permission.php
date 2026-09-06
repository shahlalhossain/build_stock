<?php

namespace App\Models;

use App\Models\Traits\Relationship\PermissionRelationship;
use App\Models\Traits\Scope\PermissionScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Traits\HasPermissions;

class Permission extends SpatiePermission
{
    use PermissionRelationship,
        PermissionScope,
        LogsActivity,
        HasPermissions,
        SoftDeletes;


    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'guard_name',
        'parent_id',
        'type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'guard_name', 'parent_id', 'name', 'description'])
            ->useLogName('Permission')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Record has been {$eventName}");
    }
}
