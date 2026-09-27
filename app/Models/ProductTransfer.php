<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductTransfer extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'product_transfers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'requisition_id',
        'source_store_id',
        'destination_store_id',
        'transaction_date',
        'remarks',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
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
            'transaction_date' => 'date',
            'requisition_id' => 'integer',
            'source_store_id' => 'integer',
            'destination_store_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_by' => 'integer',
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
            ->useLogName('product_transfer')
            ->logAll()
            ->logOnlyDirty();
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(ProductRequisition::class, 'requisition_id');
    }

    public function sourceStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    public function destinationStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'destination_store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductTransferItem::class, 'product_transfer_id');
    }

    public function receives(): HasMany
    {
        return $this->hasMany(ProductReceive::class, 'transfer_id');
    }

    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'model');
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
