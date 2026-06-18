<?php

namespace App\Contracts\Services;


use App\Models\User;

interface UserServiceInterface
{
    public function update(User $user, array $data): ?User;
}
