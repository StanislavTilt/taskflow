<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UsersController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly UserServiceInterface $userService,
    )
    {
    }

    public function update(UpdateRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);
        $user = $this->userService->update($user, $request->validated());
        return UserResource::make($user);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('show', $user);
        return UserResource::make($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->userService->destroy($user);
        return response()->json(['message' => 'User deleted']);
    }
}
