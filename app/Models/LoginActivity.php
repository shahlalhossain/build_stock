<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LoginActivity extends Model
{
    protected $table = 'login_histories';

    protected $fillable = [
        'user_id',
        'ip_address',
        'os',
        'browser',
        'device',
        'session_id',
        'login_at',
        'logout_at',
        'is_active',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The user's active record for this request: matched by session id, falling back to a
     * device match for legacy rows that were recorded without one.
     */
    public static function currentIdFor(User $user, Request $request): ?int
    {
        $active = $user->loginActivities()->where('is_active', true);

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        if ($sessionId !== null) {
            $id = (clone $active)->where('session_id', $sessionId)->latest('login_at')->value('id');

            if ($id !== null) {
                return $id;
            }
        }

        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        return $active
            ->whereNull('session_id')
            ->where('ip_address', $request->ip())
            ->where('os', $agent->platform() ?: 'Unknown')
            ->where('browser', $agent->browser() ?: 'Unknown')
            ->where('device', $agent->isDesktop() ? 'Desktop' : ($agent->device() ?: 'Unknown'))
            ->latest('login_at')
            ->value('id');
    }
}
