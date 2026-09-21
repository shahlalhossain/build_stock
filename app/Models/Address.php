<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'model_name',
        'model_id',
        'address_type_id',
        'address',
        'latitude',
        'longitude',
        'map_address',
        'landmark',
        'division_id',
        'division_name',
        'district_id',
        'district_name',
        'thana_id',
        'thana_name',
    ];

    protected $guarded = [];

    public function model() : MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_name', 'model_id');
    }

    public function addressType() : BelongsTo
    {
        return $this->belongsTo(AddressType::class, 'address_type_id');
    }
}
