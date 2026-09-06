<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Logout;

class LogoutEventListener
{
    public function handle(Logout $event): void
    {
        LoginActivity::where('user_id', $event->user->id)
            ->where('is_active', true)
            ->latest('login_at')
            ->limit(1)
            ->update(['is_active' => false, 'logout_at' => now(),]);
    }
}
