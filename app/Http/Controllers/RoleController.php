<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Roles", description: "Roles related API")]
class RoleController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    #[OA\Get(
        path: '/api/roles',
        summary: 'Get all roles',
        tags: ['Roles'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        $this->authorize('view');

        return RoleResource::collection(Role::with('permissions')->get());
    }

    #[OA\Post(
        path: '/api/roles',
        summary: 'Create a new role',
        tags: ['Roles'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'display_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string',  example: 'admin'),
                    new OA\Property(property: 'display_name', type: 'string', example: 'Administrator'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Role created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create');

        $role = Role::create($request->validated());
        return $this->success(new RoleResource($role), 'Role created successfully', 201);
    }

    #[OA\Get(
        path: '/api/roles/{role}',
        summary: 'Get a role',
        tags: ['Roles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Role not found')
        ]
    )]
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return $this->success(
            new RoleResource($role->load(['permissions'])), 
            'Roles detail found'
        );
    }

    #[OA\Put(
        path: '/api/roles/{role}',
        summary: 'Update an existing role',
        tags: ['Roles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'display_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'admin'),
                    new OA\Property(property: 'display_name', type: 'string', example: 'Administrator'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Role updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Role not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        $role->update($request->validated());
        return $this->success(new RoleResource($role), 'Role updated successfully');
    }

    #[OA\Delete(
        path: '/api/roles/{role}',
        summary: 'Delete a role',
        tags: ['Roles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Role deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Role not found')
        ]
    )]
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $role->delete();
        return $this->success(null, 'Role deleted successfully');
    }
}
