<?php

namespace Tests\Unit\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectPolicyTest extends TestCase
{
    public static function abilities(): array          // ① поставщик данных
    {
        return [
            ['show'],
            ['update'],
            ['destroy'],
        ];
    }

    #[DataProvider('abilities')]
    public function test_owner_is_allowed(string $ability): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->owner_id = 1;

        $this->assertTrue((new ProjectPolicy())->$ability($user, $project));
    }

    #[DataProvider('abilities')]
    public function test_non_owner_is_denied(string $ability): void
    {
        $user = new User();
        $user->id = 1;

        $project = new Project();
        $project->owner_id = 2;

        $this->assertFalse((new ProjectPolicy())->$ability($user, $project));
    }
}
