<?php

namespace Tests\Feature\Users;

use App\Models\User;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker, TestTrait;
    /**
     * A basic feature test example.
     */
    public function test_user_can_update_user(): void
    {
        $user = $this->actingAsUser();

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
        $this->actingAsUser();
        $otherUser = User::factory()->create();

        $this->patchJson("/api/user/$otherUser->id", [
            'name' => fake()->name(),
            'email' => fake()->email()
        ])
            ->assertStatus(403)
            ->assertJsonStructure(['message']);

    }

    public function test_user_cannot_update_info_by_invalid_email(): void
    {
        $user = $this->actingAsUser();

        $this->patchJson("/api/user/$user->id", [
            'name' => fake()->name(),
            'email' => "WRONG"
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

    }

    public function test_guest_cannot_update_other_user_info(): void
    {
        $user = User::factory()->create();

        $this->patchJson("/api/user/$user->id", [
            'name' => fake()->name(),
            'email' => fake()->email()
        ])
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}
