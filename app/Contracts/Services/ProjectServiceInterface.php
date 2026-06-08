<?php

namespace App\Contracts\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

interface ProjectServiceInterface
{
    public function getAllFor(User $user): Collection;

    public function createFor(User $user,array $data): ?Project;
    public function update(Project $project, array $data): ?Project;
    public function destroy(Project $project);
}
