<?php

namespace Tests\Feature\Projects;

use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase,TestTrait;
    /**
     * A basic feature test example.
     */
    public function test_user_can_create_project(): void
    {
        $user = $this->actingAsUser();

        $name = fake()->word();

        $this->postJson('/api/project', [
            'name' => $name,
            'description' => fake()->paragraph(),
            ])
            ->assertStatus(201)
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

        $this->assertDatabaseHas('projects', ['name' => $name,'owner_id' => $user->id]);
    }

    public function test_user_cannot_create_project_without_name(): void
    {
        $this->actingAsUser();

        $this->postJson('api/project', [
            'name' => null,
            'description' => fake()->paragraph(),
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                ]
            ]);
    }

    public function test_user_cannot_create_project_with_huge_description(): void
    {
        $this->actingAsUser();

        $this->postJson('api/project', [
            'name' => fake()->word(),
            'description' => fake()->paragraph(1001),
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'description',
                ]
            ]);
    }

    public function test_guest_cannot_create_project(): void
    {
        $this->postJson('api/project', [
            'name' => fake()->word(),
            'description' => fake()->paragraph(1001),
        ])
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

}
