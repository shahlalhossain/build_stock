<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockTransaction extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'stock_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'type',
        'store_id',
        'product_id',
        'product_variant_id',
        'transaction_date',
        'remarks',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    const TYPE_OPENING_BALANCE = 'opening_balance';

    const TYPE_ISSUE = 'issue';

    const TYPE_ADJUSTMENT = 'adjustment';

    // Purchase and Transfer have their own dedicated Modules (product-purchase,
    // product-transfer) — Stock Transaction only handles these 3 Types now.
    const TYPES = [
        self::TYPE_OPENING_BALANCE,
        self::TYPE_ISSUE,
        self::TYPE_ADJUSTMENT,
    ];

    const USER_FACING_TYPES = self::TYPES;

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
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
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
            ->useLogName('stock_transaction')
            ->logAll()
            ->logOnlyDirty();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransactionItem::class, 'stock_transaction_id');
    }

    /**
     * Primary/first line item's Product — a header-level convenience reference,
     * not the source of truth for quantities (see items()).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Primary/first line item's Product Variant — see product() note above.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function approvalLogs()
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
