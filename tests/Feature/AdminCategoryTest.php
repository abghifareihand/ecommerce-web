<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
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

    public function test_admin_can_view_categories_list(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Categories/Index')
            ->has('categories', 3)
        );
    }

    public function test_admin_can_view_create_category_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Categories/Create')
        );
    }

    public function test_admin_can_create_category_with_automatic_order(): void
    {
        Category::factory()->create(['order' => 1]);
        Category::factory()->create(['order' => 2]);

        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Elektronik Modern',
            'description' => 'Peralatan elektronik terkini.',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Elektronik Modern',
            'slug' => 'elektronik-modern',
            'description' => 'Peralatan elektronik terkini.',
            'order' => 3,
        ]);
    }

    public function test_admin_can_view_edit_category_page(): void
    {
        $category = Category::factory()->create(['name' => 'Peralatan Masak']);

        $response = $this->actingAs($this->admin)->get("/admin/categories/{$category->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Categories/Edit')
            ->where('category.id', $category->id)
            ->where('category.name', 'Peralatan Masak')
        );
    }

    public function test_admin_can_update_category_name_and_description(): void
    {
        $category = Category::factory()->create([
            'name' => 'Kategori Lama',
            'description' => 'Deskripsi lama',
            'order' => 5,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Kategori Baru Diperbarui',
            'description' => 'Deskripsi baru yang diperbarui',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Kategori Baru Diperbarui',
            'slug' => 'kategori-baru-diperbarui',
            'description' => 'Deskripsi baru yang diperbarui',
            'order' => 5,
        ]);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Kategori 1', 'order' => 1]);
        $cat2 = Category::factory()->create(['name' => 'Kategori 2', 'order' => 2]);
        $cat3 = Category::factory()->create(['name' => 'Kategori 3', 'order' => 3]);

        // Swap cat1 and cat3
        $response = $this->actingAs($this->admin)->post('/admin/categories/reorder', [
            'orders' => [
                ['id' => $cat3->id, 'order' => 1],
                ['id' => $cat2->id, 'order' => 2],
                ['id' => $cat1->id, 'order' => 3],
            ],
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success');

        $this->assertEquals(1, $cat3->fresh()->order);
        $this->assertEquals(2, $cat2->fresh()->order);
        $this->assertEquals(3, $cat1->fresh()->order);
    }

    public function test_admin_can_delete_category_safely_nulling_product_category(): void
    {
        $category = Category::factory()->create(['name' => 'Hapus Saya']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk Bertahan',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);

        // Product still exists, with category_id set to null
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => null,
        ]);
    }

    public function test_admin_can_create_product_without_category_or_with_zero(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => 0,
            'name' => 'Produk Tanpa Kategori Nol',
            'description' => 'Fleksibel tanpa kategori',
            'price' => 50000,
            'stock' => 10,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Produk Tanpa Kategori Nol',
            'category_id' => null,
        ]);
    }
}
