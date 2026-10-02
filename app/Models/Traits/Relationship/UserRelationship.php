<?php

namespace App\Models\Traits\Relationship;

use App\Models\PasswordHistory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class UserRelationship.
 */
trait UserRelationship
{
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

    /**
     * @return mixed
     */
    public function passwordHistories()
    {
        return $this->morphMany(PasswordHistory::class, 'model');
    }

    /**
     * Every Store/Warehouse this User is Attached to, regardless of Slot
     * (Manager and/or Storekeeper) — see store_user Migration.
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_user')->withPivot('role_type')->withTimestamps();
    }

    public function managedStores(): BelongsToMany
    {
        return $this->stores()->wherePivot('role_type', 'manager');
    }

    public function storekeptStores(): BelongsToMany
    {
        return $this->stores()->wherePivot('role_type', 'storekeeper');
    }
}
