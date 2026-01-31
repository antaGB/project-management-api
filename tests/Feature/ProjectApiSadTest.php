<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectApiSadTest extends TestCase
{
    use RefreshDatabase;

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
    public function test_user_biasa_tidak_bisa_update_proyek_milik_orang_lain()
    {
        // 1. Setup: Ada dua user berbeda
        $userLain = User::factory()->create();
        $pencuri = User::factory()->create();
        
        // Beri role staff ke si pencuri (tanpa permission edit)
        $staffRole = Role::create(['name' => 'staff', 'display_name' => 'Staff']);
        $pencuri->roles()->attach($staffRole);

        // 2. Buat proyek yang dimiliki user lain (atau dibuat oleh sistem)
        $project = Project::create([
            'name' => 'Proyek Rahasia',
            'status' => 'in progress'
        ]);

        // 3. Action: Si pencuri mencoba mengupdate proyek tersebut
        $response = $this->actingAs($pencuri)
                        ->putJson("/api/projects/{$project->id}", [
                            'name' => 'Nama Proyek Diubah Pencuri',
                            'status' => 'not started'
                        ]);

        // 4. Assert: Harus ditolak (403 Forbidden)
        $response->assertStatus(403);
    }
}
