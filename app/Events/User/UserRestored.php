<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRestored.
 */
class UserRestored
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
