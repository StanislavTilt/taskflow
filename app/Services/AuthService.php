<?php

namespace App\Services;


use App\Contracts\Repositories\AuthRepositoryInterface;
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
        private AuthRepositoryInterface $authRepository,
    )
    {
        //
    }

    public function register(array $data): User
    {
        return $this->authRepository->createUser($data);
    }
    public function login(string $email, string $password): User
    {
        $user = $this->authRepository->findByEmail($email);

        if(!$user || !Hash::check($password , $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        return $user;
    }
    public function logout()
    {
        request()->user()->tokens()->delete();
    }
}
