<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Name Name',
            'email' => 'example@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'example@example.com']);

        Mail::assertSent(WelcomeMail::class);
    }

    public function test_user_can_not_register_email_is_taken(): void
    {
        \App\Models\User::factory()->create(['email' => 'example@example.com']);
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Name Name',
            'email' => 'example@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);


    }

}
