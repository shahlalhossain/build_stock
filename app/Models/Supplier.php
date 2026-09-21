<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'supplier_type_id',
        'code',
        'name',
        'tin_number',
        'bin_number',
        'payment_terms_days',
        'credit_limit',
        'minimum_order_quantity',
        'minimum_order_amount',
        'lead_time_days',
        'ledger_account_id',
        'is_active',
        'status',
        'description',
        'remarks',
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
            'supplier_type_id' => 'integer',
            'payment_terms_days' => 'integer',
            'credit_limit' => 'decimal:2',
            'minimum_order_quantity' => 'integer',
            'minimum_order_amount' => 'decimal:2',
            'lead_time_days' => 'integer',
            'ledger_account_id' => 'integer',
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
            ->useLogName('supplier')
            ->logAll()
            ->logOnlyDirty();
    }

    public function supplierType(): BelongsTo
    {
        return $this->belongsTo(SupplierType::class, 'supplier_type_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(SupplierPaymentAccount::class, 'supplier_id');
    }

    public function mfsAccounts(): HasMany
    {
        return $this->hasMany(SupplierMfsAccount::class, 'supplier_id');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'model', 'model_name', 'model_id');
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
