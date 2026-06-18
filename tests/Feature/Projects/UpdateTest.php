<?php

namespace Tests\Feature\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    public function test_user_can_update_project(): void
    {
        $user = $this->actingAsUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $name = fake()->word();
        $description = fake()->paragraph();
        $status = fake()->randomElement(ProjectStatus::cases());

        $this->patchJson("/api/project/{$project->id}", [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('projects', ['name' => $name, 'description' => $description, 'status' => $status]);
    }

    public function test_user_cannot_update_project_invalid_name(): void
    {
        $user = $this->actingAsUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $name = str_repeat('a', 300);
        $description = fake()->paragraph();
        $status = fake()->randomElement(ProjectStatus::cases());

        $this->patchJson("/api/project/{$project->id}", [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                ],
            ]);
    }

    public function test_user_cannot_update_project_invalid_description(): void
    {
        $user = $this->actingAsUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $name = fake()->word();
        $description = str_repeat('a', 1001);
        $status = fake()->randomElement(ProjectStatus::cases());

        $this->patchJson("/api/project/{$project->id}", [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'description',
                ],
            ]);
    }

    public function test_user_cannot_update_project_invalid_status(): void
    {
        $user = $this->actingAsUser();
        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $name = fake()->word();
        $description = fake()->paragraph();
        $status = "WRONG";

        $this->patchJson("/api/project/{$project->id}", [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'status',
                ],
            ]);
    }

    public function test_user_cannot_update_other_user_project(): void
    {
        $user = $this->actingAsUser();
        $project = Project::factory()->create();

        $this->patchJson("/api/project/{$project->id}", [
            'name' => fake()->word(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
        ])
            ->assertStatus(403)
            ->assertJsonStructure([
                'message',
                ]);
    }

    public function test_guest_cannot_update_project(): void
    {
        $project = Project::factory()->create();
        $this->patchJson("/api/project/{$project->id}", [
            'name' => fake()->word(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
        ])
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

}
