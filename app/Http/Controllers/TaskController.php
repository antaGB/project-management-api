<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Tasks", description: "Tasks related API")]
class TaskController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    #[OA\Get(
        path: '/api/tasks',
        summary: 'Get all tasks',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        $tasks = Task::with(['assignee', 'project'])
            ->forUser(auth()->user())
            ->when($request->project_id, function($query) use ($request) {
                $query->where('project_id', $request->project_id);
            })
            ->get();

        return TaskResource::collection($tasks);
    }

    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create a new task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['project_id', 'assigned_to', 'title', 'priority', 'status'],
                properties: [
                    new OA\Property(property: 'project_id', type: 'integer',  example: 1),
                    new OA\Property(property: 'assigned_to', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Task 1'),
                    new OA\Property(property: 'description', type: 'string', example: 'Lorem ipsum dolor sit amet'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'low'),
                    new OA\Property(property: 'status', type: 'string', enum: ['to-do', 'in-progress', 'done'], example: 'to-do'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $task = Task::create($request->validated());
        return $this->success(new TaskResource($task), 'Task created successfully', 201);
    }

    #[OA\Get(
        path: '/api/tasks/{task}',
        summary: 'Get a task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function show(Task $task)
    {
        return $this->success(new TaskResource($task->load(['assignee', 'project'])), 'Task detail found');
    }

    #[OA\Put(
        path: '/api/tasks/{task}',
        summary: 'Update an existing task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['project_id', 'title', 'description', 'priority', 'status'],
                properties: [
                    new OA\Property(property: 'project_id', type: 'integer', example: 1),
                    new OA\Property(property: 'assigned_to', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Task 1'),
                    new OA\Property(property: 'description', type: 'string', example: 'Lorem ipsum dolor sit amet'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'low'),
                    new OA\Property(property: 'status', type: 'string', enum: ['to-do', 'in-progress', 'done'], example: 'to-do'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Task updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());
        return $this->success(new TaskResource($task), 'Task updated successfully');
    }

    #[OA\Delete(
        path: '/api/tasks/{task}',
        summary: 'Delete a task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Task deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found')
        ]
    )]
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();
        return $this->success(null, 'Task deleted successfully');
    }

    #[OA\Patch(
        path: '/api/tasks/{task}/status',
        summary: 'Update tasks status',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'task',
                in: 'path',
                description: 'The ID of the task',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status', 
                        type: 'string', 
                        enum: ['to-do', 'in-progress', 'done'], 
                        example: 'in-progress'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tasks status updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateStatus(Request $request, Task $task) 
    {
        $request->validate(['status' => 'required|in:to-do,in-progress,done']);
        
        $task->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated']);
    }
}
