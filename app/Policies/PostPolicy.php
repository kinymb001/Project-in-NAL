<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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
        return $user->hasRole('editor') || $user->hasPermission('create_post');
    }

    public function update(User $user, Post $post)
    {
        if ($user->hasRole('editor') || $user->hasPermission('update_post') || $user->id === $post->user_id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Post $post)
    {
        return $user->hasPermission('delete_post');
    }
}
