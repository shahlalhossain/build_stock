<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddressType extends Model
{
    protected $table = 'address_types';

    public function address() : HasMany
    {
        return $this->hasMany(Address::class, 'address_type_id');
    }
}
