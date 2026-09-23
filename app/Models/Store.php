<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'stores';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'name',
        'code',
        'type',
        'description',
        'mobile',
        'email',
        'manager_id',
        'storekeeper_id',
        'is_active',
        'status',
        'created_by',
        'updated_by',
    ];

    const TYPE_STORE = 'store';

    const TYPE_WAREHOUSE = 'warehouse';

    const TYPES = [
        self::TYPE_STORE,
        self::TYPE_WAREHOUSE,
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
            'project_id' => 'integer',
            'manager_id' => 'integer',
            'storekeeper_id' => 'integer',
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
            ->useLogName('store')
            ->logAll()
            ->logOnlyDirty();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function storekeeper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'storekeeper_id');
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'model');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'store_id');
    }

    public function isHeadOffice(): bool
    {
        return is_null($this->project_id);
    }

    public function isWarehouse(): bool
    {
        return $this->type === self::TYPE_WAREHOUSE;
    }

    public function isStore(): bool
    {
        return $this->type === self::TYPE_STORE;
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
