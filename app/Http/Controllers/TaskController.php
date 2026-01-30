<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['assignee', 'project'])
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
    public function store(Request $request)
    {
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
    public function update(Request $request, Task $task)
    {
        $task->update($request->validated());
        return $this->success(new TaskResource($task), 'Tugas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return $this->success(null, 'Tugas berhasil dihapus');
    }
}
