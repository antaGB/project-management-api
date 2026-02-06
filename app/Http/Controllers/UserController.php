<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Users", description: "Users related API")]
class UserController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    #[OA\Get(
        path: '/api/users',
        summary: 'Get all users',
        tags: ['Users'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::with('roles')->withCount('tasks')->withCount('projects')->get();
        return UserResource::collection($users);
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        tags: ['Users'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role_ids'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'pass123'),
                    new OA\Property(property: 'role_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->roles()->attach($request->role_ids);
            return $user;
        });

        return $this->success(new UserResource($user->load('roles')), 'User created successfully', 201);
    }

    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Delete a user',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function show(Request $user)
    {
        return $this->success(new UserResource($user->load(['roles', 'tasks'])), 'User detail found');
    }

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update an existing user',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role_ids'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'pass123'),
                    new OA\Property(property: 'role_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->only('name', 'email'));

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->role_ids) {
            $user->roles()->sync($request->role_ids);
        }

        return $this->success(new UserResource($user->load('roles')), 'User updated successfully');
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Delete a user',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'User deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();
        return $this->success(null, 'User deleted successfully');
    }

     #[OA\Put(
        path: 'users/{user}/assign-role',
        summary: 'Update users role',
        tags: ['Users'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['role_ids'],
                properties: [

                    new OA\Property(property: 'role_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Users role updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function assignRole(Request $request, User $user) 
    {
        $this->authorize('update', $user);

        $request->validate(['role_ids' => 'required|array|exists:roles,id']);

        $user->roles()->sync($request->role_ids);
        return $this->success($user->load('roles'), 'Users role updated successfully');
    }
}
