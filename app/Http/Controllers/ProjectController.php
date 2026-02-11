<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Projects", description: "Projects related API")]
class ProjectController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    #[OA\Get(
        path: '/api/projects',
        summary: 'Get all roles',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        $projects = Project::with('members', 'tasks')->withCount(['members', 'tasks'])->forUser(auth()->user())->paginate(10);
        return ProjectResource::collection($projects);
    }

    #[OA\Post(
        path: '/api/projects',
        summary: 'Create a new project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Project A'),
                    new OA\Property(property: 'description', type: 'string', example: 'Project description'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Project created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());
        return $this->success(new ProjectResource($project), 'Project created successfully', 201);
    }

    #[OA\Get(
        path: '/api/projects/{project}',
        summary: 'Get a project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Project not found')
        ]
    )]
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $this->success(
            new ProjectResource($project->load('members', 'tasks')->withCount(['members', 'tasks'])), 
            'Projects detail found'
        );
    }

    #[OA\Put(
        path: '/api/projects/{project}',
        summary: 'Update an existing project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Project A'),
                    new OA\Property(property: 'description', type: 'string', example: 'Project description'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Project updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Project not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());
        return $this->success(new ProjectResource($project), 'Project updated successfully');
    }

    #[OA\Delete(
        path: '/api/projects/{project}',
        summary: 'Delete a project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Project deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Project not found')
        ]
    )]
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        return $this->success(null, 'Project deleted successfully');
    }
}
