<?php

namespace App\Events\Permission;

use App\Models\Permission;
use Illuminate\Queue\SerializesModels;

/**
 * Class PermissionUpdated.
 */
class PermissionUpdated
{
    use SerializesModels;

    public $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }
}
