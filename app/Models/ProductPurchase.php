<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductPurchase extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'product_purchases';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'requisition_id',
        'store_id',
        'supplier_id',
        'transaction_date',
        'remarks',
        'invoice_number',
        'supplier_invoice_date',
        'invoice_attachment_path',
        'discount_type',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'net_amount',
        'payment_status',
        'paid_amount',
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

    const DISCOUNT_TYPE_FIXED = 'fixed';

    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    const DISCOUNT_TYPES = [
        self::DISCOUNT_TYPE_FIXED,
        self::DISCOUNT_TYPE_PERCENTAGE,
    ];

    const PAYMENT_STATUS_UNPAID = 'unpaid';

    const PAYMENT_STATUS_PARTIAL = 'partial';

    const PAYMENT_STATUS_PAID = 'paid';

    const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_UNPAID,
        self::PAYMENT_STATUS_PARTIAL,
        self::PAYMENT_STATUS_PAID,
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
            'supplier_invoice_date' => 'date',
            'requisition_id' => 'integer',
            'store_id' => 'integer',
            'supplier_id' => 'integer',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
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
            ->useLogName('product_purchase')
            ->logAll()
            ->logOnlyDirty();
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(ProductRequisition::class, 'requisition_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductPurchaseItem::class, 'product_purchase_id');
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
