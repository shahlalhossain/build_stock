<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'site_address',
        'start_date',
        'expected_end_date',
        'estimated_budget',
        'project_manager_id',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'estimated_budget' => 'decimal:2',
            'project_manager_id' => 'integer',
            'priority_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static $recordEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('project')
            ->logAll()
            ->logOnlyDirty();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'project_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'project_id');
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

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'model');
    }
}
