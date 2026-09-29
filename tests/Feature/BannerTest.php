<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_passes_active_banners_to_storefront(): void
    {
        Banner::factory()->create(['title' => 'Promo 1', 'is_active' => true]);
        Banner::factory()->create(['title' => 'Promo Inactive', 'is_active' => false]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Guest/Products/Index')
            ->has('banners', 1)
            ->where('banners.0.title', 'Promo 1')
        );
    }

    public function test_admin_can_view_banners_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Banner::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/admin/banners');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Banners/Index')
            ->has('banners', 2)
        );
    }

    public function test_admin_can_upload_new_banner(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'title' => 'Diskon Lebaran',
            'subtitle' => 'Diskon hingga 50%',
            'image' => UploadedFile::fake()->image('banner.jpg', 1200, 450),
            'link_url' => '/products',
            'order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/banners');
        $this->assertDatabaseHas('banners', [
            'title' => 'Diskon Lebaran',
            'subtitle' => 'Diskon hingga 50%',
        ]);
    }

    public function test_admin_can_view_banner_create_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/banners/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Banners/Create')
            ->has('nextOrder')
        );
    }

    public function test_admin_can_view_banner_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = Banner::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/banners/{$banner->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Banners/Edit')
            ->where('banner.id', $banner->id)
        );
    }
}
