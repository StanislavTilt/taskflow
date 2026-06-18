<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

readonly class AuthService implements AuthServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    )
    {

    }

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user  = $this->userRepository->create($data);
            $token = $user->createToken('register')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });

    }
    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if(!$user || !Hash::check($password, $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Wrong email or password.'],
            ]);
        }

        return $user;
    }
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
