<?php

namespace App\Contracts\Services;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;

interface AuthServiceInterface
{
    public function login(string $email, string $password): User;
    public function register(array $data): User;
    public function logout();
}
