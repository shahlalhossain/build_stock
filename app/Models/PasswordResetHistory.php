<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetHistory extends Model
{
    protected $fillable = [
        'user_id',
        'password_reset_code',
        'code_link_expire_at',
        'status',
        'new_password_hash', 
        'is_code_verified',
        'password_reset_at',
        'attempt_count'
    ];
}
