<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;

class PermissionController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize(ability: 'view');

        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {   
        $this->authorize(ability: 'store');

        $permission = Permission::create($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        return $this->success(
            new PermissionResource($permission->load('tasks.assignee')), 
            'Permissions detail found'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $this->authorize('update', $permission);

        $permission->update($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $this->authorize('delete', $permission);

        $permission->delete();
        return $this->success(null, 'Permission deleted successfully');
    }
}
