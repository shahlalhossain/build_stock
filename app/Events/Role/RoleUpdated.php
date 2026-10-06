<?php

namespace App\Events\Role;

use App\Models\Role;
use Illuminate\Queue\SerializesModels;

/**
 * Class PermissionUpdated.
 */
class RoleUpdated
{
    use SerializesModels;

    public $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}
