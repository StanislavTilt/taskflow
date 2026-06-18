<?php

namespace Tests\Feature\Users;

use App\Models\User;
use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, TestTrait;
    /**
     * A basic feature test example.
     */
    public function test_user_can_get_user_info(): void
    {
        $user = $this->actingAsUser();

        $this->get("/api/user/$user->id")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
    }

    public function test_user_cannot_get_other_user_info(): void
    {
        $this->actingAsUser();
        $otherUser = User::factory()->create();

        $this->get("/api/user/$otherUser->id")
            ->assertStatus(403)
            ->assertJsonStructure(['message']);
    }

    public function test_guest_cannot_get_other_user_info(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/user/$user->id")
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}
