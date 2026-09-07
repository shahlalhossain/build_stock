<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'brands';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'priority_order',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'priority_order'    => 'integer',
            'is_active'         => 'boolean',
        ];
    }

    protected static $recordEvents = [
        'created',
        'updated',
//        'statusUpdated',
//        'destroyed',
        'restored',
        'forceDeleted',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('brand')
            ->logAll()
            ->logOnlyDirty();
    }

//    public function updateStatus(string $status): bool
//    {
//        $oldStatus = $this->status;
//
//        if ($oldStatus === $status) {
//            return false;
//        }
//
//        $this->update([
//            'status' => $status,
//        ]);
//
//        activity('brand')
//            ->performedOn($this)
//            ->causedBy(auth()->user())
//            ->event('statusUpdate')
//            ->withProperties([
//                'old_status' => $oldStatus,
//                'new_status' => $status,
//            ])
//            ->log('Brand status updated');
//
//        return true;
//    }

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

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'model');
    }
}
