<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $model)
    {
        return $user->hasPermission('view_user') || $user->id === $model->id;
    }

    public function create(User $user)
    {
        return $user->hasRole('admin') || $user->hasPermission('create_user');
    }

    public function update(User $user, User $model)
    {
        if ($user->hasRole('admin') || $user->hasPermission('edit_user') || $user->id === $model->id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, User $model)
    {
        return $user->hasRole('admin') || $model->hasPermission('delete_user');
    }

    public function restore(User $user, User $model)
    {
        return $user->hasRole('admin');
    }
}
