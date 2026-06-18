<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowUserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_get_user_info(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/user/$user->id")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
    }

    public function test_user_cannot_get_other_user_info(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/user/$otherUser->id")
            ->assertStatus(403)
            ->assertJsonStructure(['message']);
    }
}
