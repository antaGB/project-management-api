<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'assigned_to', 'title', 'description','priority', 'status'];

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->hasPermission('view-tasks')) {
            return $query;
        }

        return $query->where('assigned_to', $user->id)
                    ->orWhereHas('project.members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });;
    }
}
