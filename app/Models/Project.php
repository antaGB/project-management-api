<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function tasks(): HasMany {
        return $this->hasMany(Task::class);
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->hasPermission('view-project')) {
            return $query;
        }

        return $query->whereHas('members', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
}
