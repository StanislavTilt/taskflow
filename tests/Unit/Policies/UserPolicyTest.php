<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserPolicyTest extends TestCase
{
    public static function abilities(): array          // ① поставщик данных
    {
        return [
            ['show'],
            ['update'],
        ];
    }

    #[DataProvider('abilities')]
    public function test_user_is_allowed(string $ability): void
    {
        $user = new User();
        $user->id = 1;

        $this->assertTrue((new UserPolicy())->$ability($user, $user));
    }

    #[DataProvider('abilities')]
    public function test_user_is_denied(string $ability): void
    {
        $user = new User();
        $user->id = 1;

        $otherUser = new User();
        $user->id = 2;

        $this->assertFalse((new UserPolicy())->$ability($user, $otherUser));
    }
}
