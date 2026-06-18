<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;

readonly class UserService implements UserServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    )
    {
        //
    }


    public function update(User $user,array $data): ?User
    {
        return $this->userRepository->update($user, $data);
    }

}
