<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
    public const STATE_PROPOSED = 'proposed';

    public const STATE_PLANNING = 'planning';

    public const STATE_DEVELOPING = 'developing';

    public const STATE_POSTPONED = 'postponed';

    public const STATE_COMPLETED = 'completed';

    public const STATE_OPERATIONAL = 'operational';

    public const STATE_ABANDONED = 'abandoned';

    public const CURRENT_STATES = [
        self::STATE_PROPOSED,
        self::STATE_PLANNING,
        self::STATE_DEVELOPING,
        self::STATE_POSTPONED,
        self::STATE_COMPLETED,
        self::STATE_OPERATIONAL,
        self::STATE_ABANDONED,
    ];

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'expected_end_date',
        'estimated_budget',
        'project_manager_id',
        'status',
        'current_state',
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

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'model', 'model_name', 'model_id');
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
