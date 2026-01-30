<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::withCount('tasks')->paginate(10);
        return ProjectResource::collection($projects);
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
        $project = Project::create($request->validated());
        return $this->success(new ProjectResource($project), 'Proyek berhasil dibuat', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return $this->success(
            new ProjectResource($project->load('tasks.assignee')), 
            'Detail proyek ditemukan'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());
        return $this->success(new ProjectResource($project), 'Proyek berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        return $this->success(null, 'Proyek berhasil dihapus');
    }
}
