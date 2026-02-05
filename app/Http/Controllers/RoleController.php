<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view');

        return RoleResource::collection(Role::with('permissions')->get());
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
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('store');

        $role = Role::create($request->validated());
        return $this->success(new RoleResource($role), 'Role created successfully', 201);
    }

    /**
     * Grant permission to role.
     */

    public function givePermission(Request $request, Role $role) {
        $request->validate(['permission_ids' => 'required|array|exists:permissions,id']);
        
        $role->permissions()->sync($request->permission_ids);
        return $this->success(new RoleResource($role->load('permissions')), 'Permissions berhasil diperbarui');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return $this->success(
            new RoleResource($role->load('tasks.assignee')), 
            'Roles detail found'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        $role->update($request->validated());
        return $this->success(new RoleResource($role), 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $role->delete();
        return $this->success(null, 'Role deleted successfully');
    }
}
