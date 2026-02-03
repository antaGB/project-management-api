<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreTaskRequest as TaskRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['assignee', 'project'])
            ->forUser(auth()->user())
            ->when($request->project_id, function($query) use ($request) {
                $query->where('project_id', $request->project_id);
            })
            ->get();

        return $this->success(TaskResource::collection($tasks), 'Daftar tugas berhasil diambil');
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
    public function store(TaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $task = Task::create($request->validated());
        return $this->success(new TaskResource($task), 'Tugas berhasil ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $this->success(new TaskResource($task->load(['assignee', 'project'])), 'Detail tugas ditemukan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());
        return $this->success(new TaskResource($task), 'Tugas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();
        return $this->success(null, 'Tugas berhasil dihapus');
    }

    public function updateStatus(Request $request, Task $task) 
    {
        $request->validate(['status' => 'required|in:to-do,in-progress,done']);
        
        $task->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated!']);
    }
}
