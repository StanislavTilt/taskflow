<?php

namespace Tests\Unit\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_login_returns_user_on_valid_credentials(): void
    {
        $user = new User();
        $user->password = Hash::make('password');

        $repo = Mockery::mock(UserRepositoryInterface::class);

        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('example@example.com')
            ->andReturn($user);

        $result = (new AuthService($repo))->login('example@example.com', 'password');

        $this->assertSame($user, $result);
    }

    public function test_login_throws_validation_exception_on_wrong_password(): void
    {
        $user = new User();
        $user->password = Hash::make('password');

        $repo = Mockery::mock(UserRepositoryInterface::class);

        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('example@example.com')
            ->andReturn($user);

        $this->expectException(ValidationException::class);
        (new AuthService($repo))->login('example@example.com', 'WRONG');
    }

    public function test_login_throws_when_user_not_found(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $repo->shouldReceive('findByEmail')->andReturn(null);

        $this->expectException(ValidationException::class);
        (new AuthService($repo))->login('example@example.com', 'password');
    }
}
