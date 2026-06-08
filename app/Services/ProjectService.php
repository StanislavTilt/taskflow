<?php

namespace App\Services;

use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Contracts\Services\ProjectServiceInterface;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

readonly class ProjectService implements ProjectServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    )
    {
        //
    }

    public function update(Project $project, array $data): ?Project
    {
        return $this->projectRepository->update($project, $data);
    }

    public function getAllFor(User $user): Collection
    {
        return $this->projectRepository->allFor($user);
    }

    public function createFor(User $user, array $data): ?Project
    {
        return $this->projectRepository->createFor($user, $data);
    }

    public function destroy(Project $project)
    {
        $this->projectRepository->destroy($project);
    }
}
