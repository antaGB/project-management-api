<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'priority'      => strtoupper($this->priority),
            'status'        => strtoupper($this->status),
            // Avoid null coalescing error if assignee is empty
            'assignee'      => $this->assignee ? [
                'id'        => $this->assignee->id,
                'name'      => $this->assignee->name,
            ] : null,
            'project'       => $this->whenLoaded('project', function() {
                return $this->project->name;
            }),
            'created_at'       => $this->created_at,
        ];
    }
}
