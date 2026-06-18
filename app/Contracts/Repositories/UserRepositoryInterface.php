<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function update(User $user, array $data): ?User;
    public function create(array $data): ?User;
    public function findByEmail(string $email): ?User;
}
