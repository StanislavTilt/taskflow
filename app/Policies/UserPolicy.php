<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }

    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }

    public function delete(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }
}
