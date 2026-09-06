<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $ipAddress;
    public $userAgent;
    public $device;
    public $browser;
    public $os;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $ipAddress, $userAgent, $device, $browser, $os)
    {
        $this->user         = $user;
        $this->ipAddress    = $ipAddress;
        $this->userAgent    = $userAgent;
        $this->device       = $device;
        $this->browser      = $browser;
        $this->os           = $os;
    }
}
