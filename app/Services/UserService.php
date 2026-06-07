<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;

class UserService implements UserServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    )
    {
        //
    }

    public function show(User $user): ?User
    {
        //TODO
        return $user;
    }
    public function update(User $user,array $data): ?User
    {
        return $this->userRepository->update($user, $data);
    }

    public function create(array $data): ?User
    {
        return $this->userRepository->create($data);
    }

    public function destroy(User $user)
    {
        return $this->userRepository->destroy($user);
    }
}
