<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function show(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }

    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }
}
