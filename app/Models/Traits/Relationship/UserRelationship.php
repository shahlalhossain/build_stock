<?php

namespace App\Models\Traits\Relationship;

use App\Models\User;
use App\Models\PasswordHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserRelationship.
 */
trait UserRelationship
{
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

    /**
     * @return mixed
     */
    public function passwordHistories()
    {
        return $this->morphMany(PasswordHistory::class, 'model');
    }
}
