<?php

namespace App\Services\Notification;

use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Works out WHO should receive a notification, using the "who receives it"
 * rules saved on the notification setting (specific users, roles, permissions).
 *
 * Later we can add more rule types (department, branch, manager...) here
 * without changing any other class.
 */
class ReceiverResolver
{
    /**
     * Returns a query for the users who should receive this setting's notifications.
     * It is a query (not a list) so big groups can be read in small chunks.
     * Only active users are included.
     *
     * @return Builder<User>
     */
    public function resolve(NotificationSetting $setting): Builder
    {
        $rules = $setting->receiverRules()->get();

        $userIds = $this->idsFromUserRules($rules->where('receiver_type', NotificationSettingReceiver::TYPE_USER))
            ->merge($this->idsFromRoleRules($rules->where('receiver_type', NotificationSettingReceiver::TYPE_ROLE)))
            ->merge($this->idsFromPermissionRules($rules->where('receiver_type', NotificationSettingReceiver::TYPE_PERMISSION)))
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $userIds->all())
            ->where('is_active', true);
    }

    /**
     * Reads the user ids written directly in the "user" rules.
     */
    protected function idsFromUserRules($rules)
    {
        return $rules->pluck('receiver_value')->map(fn ($id) => (int) $id);
    }

    /**
     * Finds the ids of all users who hold any of the roles in the "role" rules.
     * Role names that no longer exist are ignored.
     */
    protected function idsFromRoleRules($rules)
    {
        return $this->userIdsForRoles($rules->pluck('receiver_value')->all());
    }

    /**
     * Finds the ids of all users who hold any of the given roles (by role name).
     * Role names that do not exist are ignored.
     *
     * @param  array<int, string>  $roleNames
     * @return Collection<int, int>
     */
    public function userIdsForRoles(array $roleNames): Collection
    {
        $existing = Role::whereIn('name', $roleNames)->pluck('name');

        if ($existing->isEmpty()) {
            return collect();
        }

        return User::role($existing->all())->pluck('users.id');
    }

    /**
     * Finds the ids of all users who hold any of the permissions in the
     * "permission" rules (given directly or through a role).
     * Permission names that no longer exist are ignored.
     */
    protected function idsFromPermissionRules($rules)
    {
        $permissionNames = Permission::whereIn('name', $rules->pluck('receiver_value'))->pluck('name');

        if ($permissionNames->isEmpty()) {
            return collect();
        }

        return User::permission($permissionNames->all())->pluck('users.id');
    }
}
