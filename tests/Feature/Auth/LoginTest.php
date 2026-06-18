<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_login_and_receives_token(): void
    {
        \App\Models\User::factory()
            ->create(['email' => 'example@example.com', 'password' => 'password']);
        $this->postJson('/api/auth/login', [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'email','name'],'token']);
    }

    public function test_user_cannot_login_wrong_email_or_password(): void
    {
        \App\Models\User::factory()
            ->create(['email' => 'example@example.com', 'password' => 'password']);

        $this->postJson('/api/auth/login', [
            'email' => 'example@example.com',
            'password' => 'WRONG',
        ])->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }
}
