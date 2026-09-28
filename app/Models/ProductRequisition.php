<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductRequisition extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'product_requisitions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'store_id',
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
            'store_id' => 'integer',
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
            ->useLogName('product_requisition')
            ->logAll()
            ->logOnlyDirty();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductRequisitionItem::class, 'product_requisition_id');
    }

    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'model');
    }

    /**
     * Approved Requisitions that still have at least one Line Item with
     * Remaining (un-Purchased) Quantity — i.e. eligible for "Purchase against
     * Requisition". Fully Purchased Requisitions are excluded.
     */
    public function scopeAvailableForPurchase($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->whereHas('items', function ($itemQuery) {
                $itemQuery->whereRaw(
                    'quantity > (select coalesce(sum(ppi.quantity), 0) '.
                    'from product_purchase_items as ppi '.
                    'inner join product_purchases as pp on pp.id = ppi.product_purchase_id '.
                    'where ppi.requisition_item_id = product_requisition_items.id '.
                    'and pp.status = ?)',
                    [ProductPurchase::STATUS_APPROVED]
                );
            });
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
