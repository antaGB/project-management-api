<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskApiTest extends TestCase
{
    /** @test */
    public function test_membuat_task_dan_menugaskannya_ke_user()
    {
        // Beri permission 'create-task'
        $role = Role::create(['name' => 'manager', 'display_name' => 'Manager']);
        $permission = Permission::create(['name' => 'create-task', 'description' => 'permission']);
        $role->permissions()->attach($permission);
        
        $manager = User::factory()->create();
        $manager->roles()->attach($role);

        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($manager)
                        ->postJson("/api/tasks", [
                            'project_id' => $project->id,
                            'assigned_to' => $user->id,
                            'title' => 'Selesaikan Laporan Keuangan',
                            'priority' => 'medium',
                            'status' => 'to-do'
                        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['assigned_to' => $user->id, 'status' => 'to-do']);
    }

    /** @test */
    public function test_input_status_task_harus_valid()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
                        ->postJson("/api/tasks", [
                            'project_id' => $project->id,
                            'title' => 'Task Baru',
                            'priority' => 'low',
                            'status' => 'random'
                        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function test_user_bisa_mengupdate_status_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $user->id, 'status' => 'to-do']);

        $response = $this->actingAs($user)
                        ->patchJson("/api/tasks/{$task->id}/status", [
                            'status' => 'in-progress'
                        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in-progress'
        ]);
    }

    /** @test */
    public function test_detail_project_menampilkan_semua_task_terkait()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $project->members()->attach($user);
        // Buat 3 task untuk project ini
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
                        ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.tasks'); // Memastikan ada 3 task di dalam array tasks
    }

    /** @test */
    public function test_alur_lengkap_pembuatan_proyek_sampai_task()
    {
        $user = User::factory()->create();
        $permissionProject = Permission::create(['name' => 'create-project', 'description' => 'Create Project']);
        $permissionTask = Permission::create(['name' => 'create-task', 'description' => 'Create task']);
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $role->permissions()->attach($permissionProject);
        $role->permissions()->attach($permissionTask);
        $user->roles()->attach($role);


        // 1. Buat Project
        $project = Project::create(['name' => 'project raksasa']);

        $project->members()->attach($user->id);

        // 2. Buat Task untuk Project tersebut
        $taskResponse = $this->actingAs($user)->postJson('/api/tasks', [
            'project_id' => $project->id,
            'title' => 'Task Pertama',
            'assigned_to' => $user->id,
            'status' => 'to-do',
            'priority' => 'low',
        ]);

        $taskResponse->assertStatus(201);
        
        // 3. Verifikasi di Endpoint Project Detail
        $this->getJson("/api/projects/{$project->id}")
            ->assertJsonFragment(['title' => 'Task Pertama']);
    }

    /** @test */
    public function test_user_tidak_bisa_membuat_task_di_project_yang_tidak_diikuti()
    {
        $user = User::factory()->create(); // User tanpa akses project
        $project = Project::factory()->create(); // Project milik orang lain

        $response = $this->actingAs($user)
                        ->postJson("/api/tasks", [
                            'project_id' => $project->id,
                            'title' => 'Coba-coba akses',
                            'assigned_to' => $user->id,
                            'status' => 'to-do',
                            'priority' => 'low',
                        ]);

        // Jika Anda menerapkan Policy akses project, ini harus 403
        $response->assertStatus(403);
    }

    /** @test */
    public function test_api_resource_mengembalikan_nama_user_bukan_sekadar_id()
    {
        $user = User::factory()->create(['name' => 'Budi Sudarsono']);
        $task = Task::factory()->create(['assigned_to' => $user->id]);

        $response = $this->actingAs($user)
                        ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.assignee.name', 'Budi Sudarsono');
    }

    /** @test */
    public function user_tidak_bisa_ditugaskan_ke_task_jika_bukan_member_project()
    {
        $manager = User::factory()->create();
        $project = Project::factory()->create();
        $nonMember = User::factory()->create();

        // Manager masuk ke project, tapi $nonMember tidak.
        $project->members()->attach($manager->id);

        $response = $this->actingAs($manager)->postJson('/api/tasks', [
            'project_id' => $project->id,
            'title' => 'Tugas Rahasia',
            'assigned_to' => $nonMember->id, // Ini harusnya gagal
            'status' => 'to-do',
            'priority' => 'low'
        ]);

        // Anda bisa mengembalikan 403 (Forbidden) atau 422 (Validation Error)
        $response->assertStatus(403); 
    }

    /** @test */
    public function user_hanya_bisa_melihat_daftar_project_yang_diikuti()
    {
        $user = User::factory()->create();
        $myProject = Project::factory()->create(['name' => 'Project Saya']);
        $otherProject = Project::factory()->create(['name' => 'Project Orang Lain']);

        // User hanya join ke 'Project Saya'
        $myProject->members()->attach($user);
        // $otherProject->members()->attach($user);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data') // Hanya boleh ada 1
                ->assertJsonFragment(['project_name' => 'Project Saya'])
                ->assertJsonMissing(['project_name' => 'Project Orang Lain']);
    }

    /** @test */
    public function user_dilarang_melihat_detail_project_yang_tidak_diikuti()
    {
        $user = User::factory()->create();
        $secretProject = Project::factory()->create(); // User bukan member di sini
        // $secretProject->members()->attach($user);

        $response = $this->actingAs($user)->getJson("/api/projects/{$secretProject->id}");

        $response->assertStatus(403);
    }
}
