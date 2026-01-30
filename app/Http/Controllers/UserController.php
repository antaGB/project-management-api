<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->withCount('tasks')->get();
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->roles()->attach($request->role_ids);
            return $user;
        });

        return $this->success(new UserResource($user->load('roles')), 'User berhasil dibuat', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->success(new UserResource($user->load(['roles', 'tasks'])), 'Data user ditemukan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->only('name', 'email'));

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->role_ids) {
            $user->roles()->sync($request->role_ids);
        }

        return $this->success(new UserResource($user->load('roles')), 'Data user diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('update', $user);

        $user->delete();
        return $this->success(null, 'User berhasil dihapus');
    }

     /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user) 
    {
        $request->validate(['role_ids' => 'required|array|exists:roles,id']);

        $user->roles()->sync($request->role_ids);
        return $this->success($user->load('roles'), 'Role user berhasil diperbarui');
    }
}
