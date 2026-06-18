<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function __construct(
        private readonly AuthServiceInterface $authService
    )
    {

    }

    public function register(RegisterRequest $request): JsonResponse
    {
        ['user' => $user, 'token' => $token] = $this->authService->register($request->validated());

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->email, $request->password);
        $token = $user->createToken('login')->plainTextToken;
        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response();
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out']);
    }

}
