<?php

namespace App\Events\Permission;

use App\Models\Permission;
use Illuminate\Queue\SerializesModels;

/**
 * Class PermissionCreated.
 */
class PermissionCreated
{
    use SerializesModels;

    /**
     * @var
     */
    public $permission;

    /**
     * @param $permission
     */
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }
}
