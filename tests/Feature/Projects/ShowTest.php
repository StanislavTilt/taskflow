<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase,TestTrait;

    public function test_user_can_get_project_info(): void
    {
        $user = $this->actingAsUser();

        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->get("/api/project/{$project->id}")
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
    }

    public function test_user_cannot_get_project_info_without_permission(): void
    {

        $this->actingAsUser();

        $otherUserProject = Project::factory()->create();

        $this->get("/api/project/{$otherUserProject->id}")
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_guest_cannot_get_project(): void
    {
        $project = Project::factory()->create();

        $this->getJson("/api/project/{$project->id}")
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

}
