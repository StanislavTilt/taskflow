<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user,array $data): ?User
    {
        $user->update($data);
        return $user->refresh();
    }

    public function create(array $data): ?User
    {
        return User::create($data);
    }

    public function destroy(User $user)
    {
        return $user->delete();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
