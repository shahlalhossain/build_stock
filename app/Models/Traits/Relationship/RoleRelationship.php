<?php

namespace App\Models\Traits\Relationship;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait RoleRelationship
{
    // Define the permissions relationship if using Spatie's package
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter() : BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
