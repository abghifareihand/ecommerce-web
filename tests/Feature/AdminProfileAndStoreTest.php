<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProfileAndStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::create([
            'name' => 'EcoStore Test',
            'phone' => '08985454555',
            'address' => 'Sleman Yogyakarta',
        ]);
    }

    public function test_admin_can_view_profile_and_store_settings_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $responseProfile = $this->actingAs($admin)->get('/admin/profile');
        $responseProfile->assertStatus(200);

        $responseStore = $this->actingAs($admin)->get('/admin/store');
        $responseStore->assertStatus(200);
    }

    public function test_admin_can_update_profile_and_upload_avatar(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($admin)->post('/admin/profile', [
            'name' => 'New Name Admin',
            'email' => 'new@example.com',
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $admin->refresh();

        $this->assertEquals('New Name Admin', $admin->name);
        $this->assertEquals('new@example.com', $admin->email);
        $this->assertNotNull($admin->avatar);
        Storage::disk('public')->assertExists($admin->avatar);
    }

    public function test_admin_can_update_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect();
        $admin->refresh();

        $this->assertTrue(Hash::check('newpassword456', $admin->password));
    }

    public function test_admin_can_update_store_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put('/admin/store', [
            'name' => 'Toko Kriya Nusantara',
            'tagline' => 'Kerajinan Tangan Terbaik Indonesia',
            'phone' => '081234567890',
            'email' => 'cs@kriyanusantara.com',
            'address' => 'Jl. Malioboro No. 45, Yogyakarta',
            'description' => 'Toko kerajinan tangan lokal kualitas ekspor.',
        ]);

        $response->assertRedirect();

        $store = StoreSetting::first();
        $this->assertEquals('Toko Kriya Nusantara', $store->name);
        $this->assertEquals('081234567890', $store->phone);
        $this->assertEquals('6281234567890', $store->clean_phone);
        $this->assertEquals('Jl. Malioboro No. 45, Yogyakarta', $store->address);
    }

    public function test_admin_can_upload_and_delete_store_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $logoFile = UploadedFile::fake()->image('store_logo.png', 300, 300);

        $response = $this->actingAs($admin)->post('/admin/store', [
            'name' => 'EcoStore Updated',
            'phone' => '08985454555',
            'logo' => $logoFile,
        ]);

        $response->assertRedirect();

        $store = StoreSetting::first();
        $this->assertNotNull($store->logo);
        $this->assertNotNull($store->logo_url);
        Storage::disk('public')->assertExists($store->logo);

        // Test delete logo
        $deleteResponse = $this->actingAs($admin)->delete('/admin/store/logo');
        $deleteResponse->assertRedirect();

        $store->refresh();
        $this->assertNull($store->logo);
        $this->assertEquals(asset('assets/img/logo.png'), $store->logo_url);
    }

    public function test_guest_cannot_access_profile_or_store_settings(): void
    {
        $response = $this->get('/admin/profile');
        $response->assertRedirect('/admin/login');

        $responseStore = $this->get('/admin/store');
        $responseStore->assertRedirect('/admin/login');
    }
}
