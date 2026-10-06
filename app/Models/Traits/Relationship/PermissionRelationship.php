<?php

namespace App\Models\Traits\Relationship;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PermissionRelationship.
 */
trait PermissionRelationship
{
    /**
     * @return mixed
     */
    public function parent()
    {
        return $this->belongsTo(__CLASS__, 'parent_id')->with('parent');
    }

    /**
     * @return mixed
     */
    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent_id')->with('children');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
