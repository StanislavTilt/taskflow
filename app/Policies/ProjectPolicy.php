<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Create a new policy instance.
     */
    public function show(User $currentUser, Project $project): bool
    {
        return $currentUser->id === $project->owner_id;
    }

    public function update(User $currentUser, Project $project): bool
    {
        return $currentUser->id === $project->owner_id;
    }

    public function destroy(User $currentUser, Project $project): bool
    {
        return $currentUser->id === $project->owner_id;
    }
}
