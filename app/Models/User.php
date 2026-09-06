<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\Attribute\UserAttribute;
use App\Models\Traits\Method\UserMethod;
use App\Models\Traits\Relationship\UserRelationship;
use App\Models\Traits\Scope\UserScope;

class User extends Authenticatable
{
    use HasPermissions,
        HasRoles,
        HasApiTokens,
        Notifiable,
        UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope,
        SoftDeletes,
        LogsActivity;

    public const TYPE_ADMIN = 'admin';
    public const TYPE_USER = 'user';

    protected $fillable = [
        'type',
        'username',
        'name',
        'mobile',
        'email',
        'password',
        'is_active',
        'is_mobile_verified',
        'mobile_verified_at',
        'is_email_verified',
        'email_verified_at',
        'registration_platform',
        'profile_picture',
        'created_by',
        'updated_by',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'mobile_verified_at'    => 'datetime',
            'password'              => 'hashed',
        ];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
    public function passwordResetHistory()
    {
        return $this->hasMany(PasswordResetHistory::class, 'user_id', 'id');
    }
}
