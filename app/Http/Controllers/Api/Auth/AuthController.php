<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{

    public function __construct(
        private readonly AuthServiceInterface $authService
    )
    {

    }

    public function register(RegisterRequest $request): UserResource
    {
        $user = $this->authService->register($request->validated());
        return $this->respondWithToken($user,'register');
    }

    public function login(LoginRequest $request): UserResource
    {
        $user = $this->authService->login($request->email, $request->password);
        return $this->respondWithToken($user,'login');

    }

    public function logout()
    {
        $this->authService->logout();
        return response()->json(['message' => 'Logged out']);
    }

    private function respondWithToken(User $user, string $name)
    {
        $token = $user->createToken($name)->plainTextToken;
        return UserResource::make($user)->additional(['token' => $token]);
    }

}
