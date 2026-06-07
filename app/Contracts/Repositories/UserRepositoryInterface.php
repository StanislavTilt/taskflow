<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function update(User $user, array $data): ?User;
    public function create(array $data): ?User;
    public function destroy(User $user);
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
}
