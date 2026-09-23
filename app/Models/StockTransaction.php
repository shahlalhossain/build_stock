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
        'supplier_id',
        'linked_transaction_id',
        'transaction_date',
        'remarks',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    const TYPE_OPENING_BALANCE = 'opening_balance';

    const TYPE_PURCHASE = 'purchase';

    const TYPE_ISSUE = 'issue';

    const TYPE_ADJUSTMENT = 'adjustment';

    const TYPE_TRANSFER_OUT = 'transfer_out';

    const TYPE_TRANSFER_IN = 'transfer_in';

    const TYPES = [
        self::TYPE_OPENING_BALANCE,
        self::TYPE_PURCHASE,
        self::TYPE_ISSUE,
        self::TYPE_ADJUSTMENT,
        self::TYPE_TRANSFER_OUT,
        self::TYPE_TRANSFER_IN,
    ];

    // User-facing types accepted on create/update forms. "transfer" is the single
    // public type; the service internally splits it into TYPE_TRANSFER_OUT/TYPE_TRANSFER_IN.
    const TYPE_TRANSFER = 'transfer';

    const USER_FACING_TYPES = [
        self::TYPE_OPENING_BALANCE,
        self::TYPE_PURCHASE,
        self::TYPE_ISSUE,
        self::TYPE_ADJUSTMENT,
        self::TYPE_TRANSFER,
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
            'supplier_id' => 'integer',
            'linked_transaction_id' => 'integer',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function linkedTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class, 'linked_transaction_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransactionItem::class, 'stock_transaction_id');
    }

    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'model');
    }

    public function isTransfer(): bool
    {
        return in_array($this->type, [self::TYPE_TRANSFER_OUT, self::TYPE_TRANSFER_IN], true);
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
