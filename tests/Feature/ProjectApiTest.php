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

     /** @test */
    public function test_validasi_gagal_saat_input_proyek_kosong()
    {
        $admin = User::factory()->create();
        $admin->roles()->create(['name' => 'admin', 'display_name' => 'Admin']);

        // Mengirim data kosong ke endpoint store
        $response = $this->actingAs($admin)
                        ->postJson('/api/projects', []);

        // Laravel otomatis mengembalikan 422 jika FormRequest gagal
        $response->assertStatus(422);
        
        // Memastikan ada pesan error untuk field 'name'
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function test_user_tanpa_permission_tidak_bisa_update_proyek()
    {
        // 1. Setup: user
        $user = User::factory()->create();
        
        // Beri role staff ke si user (tanpa permission edit)
        $staffRole = Role::create(['name' => 'staff', 'display_name' => 'Staff']);
        $user->roles()->attach($staffRole);


        // 2. Buat proyek 
        $project = Project::create([
            'name' => 'Proyek Rahasia'
        ]);

        // 3. Action: Si user mencoba mengupdate proyek tersebut
        $response = $this->actingAs($user)
                        ->putJson("/api/projects/{$project->id}", [
                            'name' => 'Nama Proyek Diubah Pencuri'
                        ]);

        // 4. Assert: Harus ditolak (403 Forbidden)
        $response->assertStatus(403);
    }


}
