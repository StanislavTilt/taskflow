<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly UserServiceInterface $userService,
    )
    {
    }

    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user = $this->userService->update($user, $request->validated());
        return UserResource::make($user)
            ->response();
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('show', $user);
        return UserResource::make($user)
            ->response();
    }
}
