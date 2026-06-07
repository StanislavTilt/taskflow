<?php

namespace App\Contracts\Services;


use App\Models\User;

interface UserServiceInterface
{
    public function update(User $user, array $data): ?User;
    public function create(array $data): ?User;
    public function destroy(User $user);
    public function show(User $user): ?User;
}
