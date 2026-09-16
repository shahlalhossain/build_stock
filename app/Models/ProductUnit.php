<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductUnit extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'product_units';

    public const GROUP_QUANTITY = 'Quantity';

    public const GROUP_WEIGHT = 'Weight';

    public const GROUP_VOLUME = 'Volume';

    public const GROUP_AREA = 'Area';

    public const GROUP_LENGTH = 'Length';

    public const GROUP_PACKAGING = 'Packaging';

    public const GROUP_STRUCTURAL = 'Structural';

    public const GROUP_LOGISTICS = 'Logistics';

    public const GROUPS = [
        self::GROUP_QUANTITY,
        self::GROUP_WEIGHT,
        self::GROUP_VOLUME,
        self::GROUP_AREA,
        self::GROUP_LENGTH,
        self::GROUP_PACKAGING,
        self::GROUP_STRUCTURAL,
        self::GROUP_LOGISTICS,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'name',
        'symbol',
        'description',
        'usage',
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
            ->useLogName('product_unit')
            ->logAll()
            ->logOnlyDirty();
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
