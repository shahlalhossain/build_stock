<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The address of one user's browser or phone, used to send push notifications.
 */
class UserDeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_name',
        'is_active',
        'last_used_at',
    ];

    /**
     * The token is a secret-like value, so it is hidden when the model is turned into JSON.
     *
     * @var list<string>
     */
    protected $hidden = ['token'];

    /**
     * Tells Laravel how to read these columns (true/false and date).
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * Only the tokens that can still be used.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The user who owns this device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Returns the token with most of it hidden (safe to show in logs and screens).
     */
    public function maskedToken(): string
    {
        return '***'.substr($this->token, -6);
    }
}
