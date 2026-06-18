<?php

namespace App\Contracts\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

interface ProjectRepositoryInterface
{
    public function allFor(User $user): Collection;
    public function createFor(User $user,array $data): ?Project;
    public function update(Project $project, array $data): ?Project;
    public function destroy(Project $project): void;
}
