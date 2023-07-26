<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function create(User $user)
    {
        return $user->hasRole('editor') || $user->hasPermission('create_category');
    }

    public function update(User $user, Category $category)
    {
        if ($user->hasRole('editor') || $user->hasPermission('update_category') || $user->id === $category->user_id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Category $category)
    {
        return $user->hasPermission('delete_category');
    }
}
