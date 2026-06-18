<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * A basic feature test example.
     */
    public function test_user_can_update_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newName = fake()->name();
        $newEmail = fake()->email();

        $this->patchJson("/api/user/$user->id", [
            'name' => $newName,
            'email' => $newEmail,
        ])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', ['name' => $newName, 'email' => $newEmail]);
    }

    public function test_user_cannot_update_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->patchJson("/api/user/$otherUser->id", [
            'name' => fake()->name(),
            'email' => fake()->email()
        ])
            ->assertStatus(403)
            ->assertJsonStructure(['message']);

    }
}
