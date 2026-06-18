<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\User;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase,TestTrait;
    /**
     * A basic feature test example.
     */
    public function test_user_gets_own_projects(): void
    {
        $user = $this->actingAsUser();
        $other = User::factory()->create();
        Project::factory(5)->create(['owner_id' => $user->id]);
        Project::factory(3)->create(['owner_id' => $other->id]);

        $this->getJson('api/project')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'status',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);

    }

    public function test_guest_cannot_list_projects(): void
    {
        $this->getJson('api/project')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }
}
