<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
    public function store(Request $request)
    {
        $role = Role::create($request->validated());
        return $this->success(new RoleResource($role), 'Role berhasil dibuat', 201);
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
        //
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
    public function update(Request $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
