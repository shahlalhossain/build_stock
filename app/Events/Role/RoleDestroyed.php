<?php

namespace App\Events\Role;

use App\Models\Role;
use Illuminate\Queue\SerializesModels;

/**
 * Class PermissionCreated.
 */
class RoleDestroyed
{
    use SerializesModels;

    /**
     * @var
     */
    public $role;

    /**
     * @param $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}
