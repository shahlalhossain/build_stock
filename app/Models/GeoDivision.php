<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;

class GeoDivision extends Model
{
//    use SoftDeletes;

    protected $table = 'location_divisions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name_en',
        'name_bn',
        'description_en',
        'description_bn',
        'latitude',
        'longitude',
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
        return ['created_at' => 'datetime', 'updated_at' => 'datetime',];
    }

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }


    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter() : BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
