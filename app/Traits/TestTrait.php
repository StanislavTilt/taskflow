<?php

namespace App\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait TestTrait
{
    private function actingAsUser(): User
    {
        Sanctum::actingAs($user = User::factory()->create());
        return $user;
    }
}
