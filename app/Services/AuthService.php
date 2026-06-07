<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

readonly class AuthService implements AuthServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
    )
    {

    }

    public function register(array $data): User
    {
        return $this->userRepository->create($data);
    }
    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if(!$user || !Hash::check($password , $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Wrong email or password.'],
            ]);
        }

        return $user;
    }
    public function logout()
    {
        request()->user()->tokens()->delete();
    }
}
