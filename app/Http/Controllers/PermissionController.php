<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Permissions", description: "Permissions related API")]
class PermissionController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    #[OA\Get(
        path: '/api/permissions',
        summary: 'Get all permissions',
        tags: ['Permissions'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        $this->authorize(ability: 'view');

        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    #[OA\Post(
        path: '/api/permissions',
        summary: 'Create a new permission',
        tags: ['Permissions'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'permission-name'),
                    new OA\Property(property: 'description', type: 'string', example: 'Permission description'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Permission created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StorePermissionRequest $request)
    {   
        $this->authorize(ability: 'store');

        $permission = Permission::create($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission created successfully', 201);
    }

    #[OA\Get(
        path: '/api/permissions/{permission}',
        summary: 'Get a permission',
        tags: ['Permissions'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'permission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Permission not found')
        ]
    )]
    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        return $this->success(
            new PermissionResource($permission), 
            'Permissions detail found'
        );
    }

    #[OA\Put(
        path: '/api/permissions/{permission}',
        summary: 'Update an existing permission',
        tags: ['Permissions'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'permission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'permission-name'),
                    new OA\Property(property: 'description', type: 'string', example: 'Permission description'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Permission updated'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'permission not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $this->authorize('update', $permission);

        $permission->update($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission updated successfully');
    }

    #[OA\Delete(
        path: '/api/permissions/{permission}',
        summary: 'Delete a permission',
        tags: ['Permissions'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'permission', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Permission deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Permission not found')
        ]
    )]
    public function destroy(Permission $permission)
    {
        $this->authorize('delete', $permission);

        $permission->delete();
        return $this->success(null, 'Permission deleted successfully');
    }
}
