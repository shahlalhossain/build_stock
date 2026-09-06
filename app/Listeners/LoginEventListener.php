<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Login;
use Jenssegers\Agent\Agent;

class LoginEventListener
{
    public function handle(Login $event): void
    {
        $request    = request();
        $agent      = new Agent();

        $agent->setUserAgent($request->userAgent());

        LoginActivity::create([
            'user_id'    => $event->user->id,
            'ip_address' => $request->ip(),
            'os'         => $agent->platform() ?? 'Unknown',
            'browser'    => $agent->browser() ?? 'Unknown',
            'device'     => $agent->isDesktop() ? 'Desktop' : ($agent->device() ?: 'Unknown'),
            'login_at'   => now(),
            'is_active'  => true,
        ]);
    }
}
