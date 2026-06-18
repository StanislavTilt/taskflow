<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    public function test_user_can_delete_project(): void
    {
        $user = $user = $this->actingAsUser();;
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->deleteJson("api/project/$project->id")
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_user_cannot_delete_project_if_not_owner(): void
    {
        $this->actingAsUser();
        $project = Project::factory()->create();

        $this->deleteJson("api/project/$project->id")
            ->assertStatus(403)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_guest_cannot_delete_project(): void
    {
        $project = Project::factory()->create();

        $this->deleteJson("api/project/$project->id")
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }
}
