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

class UserController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view');

        $users = User::with('roles')->withCount('tasks')->withCount('projects')->get();
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Request $user)
    {
        return $this->success(new UserResource($user->load(['roles', 'tasks'])), 'User detail found');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('edit', $user);

        $user->update($request->only('name', 'email'));

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->role_ids) {
            $user->roles()->sync($request->role_ids);
        }

        return $this->success(new UserResource($user->load('roles')), 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();
        return $this->success(null, 'User deleted successfully');
    }

     /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user) 
    {
        $this->authorize('edit', $user);

        $request->validate(['role_ids' => 'required|array|exists:roles,id']);

        $user->roles()->sync($request->role_ids);
        return $this->success($user->load('roles'), 'Users role updated successfully');
    }
}
