<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => fake()->text(20),
            'description' => fake()->paragraph(),
            'project_id' => Project::factory(),
            'created_by_id' => User::factory(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
        ];
    }
}
