<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use App\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_tanpa_token_ditolak_aksesnya()
    {
        $response = $this->getJson('/api/projects');

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function staff_mencoba_menghapus_data_milik_admin_dilarang()
    {
        // 1. Setup: Buat Role Staff dan Permission terbatas
        $staffRole = Role::create(['name' => 'staff', 'display_name' => 'Staff']);
        $staff = User::factory()->create();
        $staff->roles()->attach($staffRole);

        // 2. Buat Project
        $project = Project::create([
            'name' => 'Admin Project'
        ]);

        // 3. Action: Login sebagai Staff dan coba hapus
        $response = $this->actingAs($staff)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function admin_berhasil_menambah_data_baru()
    {
        // 1. Setup: Buat Role Admin & Permission 'create-project'
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $permission = Permission::create(['name' => 'create-project']);
        $adminRole->permissions()->attach($permission);

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        // 2. Action: Kirim data project baru
        $response = $this->actingAs($admin)
                         ->postJson('/api/projects', [
                             'name' => 'Project Baru dari Test',
                             'description' => 'Testing description',
                         ]);

        // 3. Assert: Cek status 201 dan data masuk DB
        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'Project Baru dari Test']);
    }
}
