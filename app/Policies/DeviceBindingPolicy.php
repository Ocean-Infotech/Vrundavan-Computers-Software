<?php

namespace App\Policies;

use App\Models\DeviceBinding;
use App\Models\User;

class DeviceBindingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'admin']));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeviceBinding $deviceBinding): bool
    {
        return $user->is_admin === true || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'admin']));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeviceBinding $deviceBinding): bool
    {
        return $user->is_admin === true || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'admin']));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeviceBinding $deviceBinding): bool
    {
        return $user->is_admin === true || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'admin']));
    }
}
