<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Logout;

class LogoutEventListener
{
    public function handle(Logout $event): void
    {
        $loginActivityId = LoginActivity::currentIdFor($event->user, request())
            ?? LoginActivity::where('user_id', $event->user->id)
                ->where('is_active', true)
                ->latest('login_at')
                ->value('id');

        if ($loginActivityId === null) {
            return;
        }

        LoginActivity::whereKey($loginActivityId)->update(['is_active' => false, 'logout_at' => now()]);
    }
}
