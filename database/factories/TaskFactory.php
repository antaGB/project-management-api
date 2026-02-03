<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(),
            'title' => fake()->name(),
            'description' => fake()->text(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => fake()->randomElement(['to-do', 'in-progress', 'done']),
        ];
    }
}
