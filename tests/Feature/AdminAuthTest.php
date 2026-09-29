<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_customer_user_cannot_access_admin_area(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
