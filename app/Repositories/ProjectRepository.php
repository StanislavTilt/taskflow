<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

readonly class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * Create a new class instance.
     */

    public function allFor(User $user): Collection
    {
        return $user->ownedProjects()->get();
    }

    public function createFor(User $user,array $data): ?Project
    {
        return $user->ownedProjects()->create($data)->fresh();
    }

    public function update(Project $project, array $data): ?Project
    {
        $project->update($data);
        return $project;
    }

    public function destroy(Project $project)
    {
        $project->delete();
    }
}
