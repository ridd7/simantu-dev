<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function admin(User $user)
    {
        return $user->level_user === 'admin';
    }

    public function user(User $user)
    {
        return $user->level_user === 'user';
    }
}
