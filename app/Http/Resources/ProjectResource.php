<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_name' => $this->name,
            'info' => $this->description,
            'status' => ucfirst($this->status),
            'tasks_count' => $this->tasks_count, // Data from withCount()
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
