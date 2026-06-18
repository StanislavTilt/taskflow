<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ProjectServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\CreateRequest;
use App\Http\Requests\Projects\UpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly ProjectServiceInterface $projectService,
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->getAllFor($request->user());
        return ProjectResource::collection($projects)
            ->response();
    }

    public function store(CreateRequest $request): JsonResponse
    {
        $project = $this->projectService->createFor($request->user(),$request->validated());
        return ProjectResource::make($project)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('show', $project);
        return ProjectResource::make($project)
            ->response();
    }

    public function update(UpdateRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project = $this->projectService->update($project, $request->validated());
        return ProjectResource::make($project)
            ->response();
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('destroy', $project);
        $this->projectService->destroy($project);
        return response()->json(['message' => 'Project deleted']);
    }


}
