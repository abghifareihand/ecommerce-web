<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_dashboard_with_stats(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.total_products', 2)
            ->where('stats.active_products', 1)
            ->where('stats.inactive_products', 1)
        );
    }

    public function test_admin_can_view_products_list(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/Index')
            ->has('products.data', 5)
        );
    }

    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-product.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Natural Oak Shelf',
            'description' => 'A clean modern wooden shelf.',
            'price' => 89.99,
            'stock' => 20,
            'image' => $file,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Natural Oak Shelf',
            'slug' => 'natural-oak-shelf',
            'price' => 89.99,
            'stock' => 20,
            'is_active' => true,
        ]);

        $product = Product::where('slug', 'natural-oak-shelf')->first();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Old Product Name',
            'price' => 50.00,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'description' => 'Updated description content.',
            'price' => 65.50,
            'stock' => 25,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 65.50,
            'stock' => 25,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        Storage::fake('public');

        $filePath = 'products/sample.jpg';
        Storage::disk('public')->put($filePath, 'fake content');

        $product = Product::factory()->create([
            'name' => 'Delete Me',
            'image' => $filePath,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);

        Storage::disk('public')->assertMissing($filePath);
    }
}
