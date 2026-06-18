<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    public function login(string $email, string $password): User;
    public function register(array $data): array;
    public function logout(User $user): void;
}
