<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_can_be_rendered_as_brand_showcase(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing/Index')
        );
    }

    public function test_product_catalog_displays_active_products_only(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);
        Product::factory()->count(2)->create(['is_active' => false]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Guest/Products/Index')
            ->has('products.data', 3)
        );
    }

    public function test_product_detail_page_renders_for_active_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Ceramic Mug',
            'slug' => 'ceramic-mug',
            'is_active' => true,
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Guest/Products/Show')
            ->where('product.id', $product->id)
            ->where('product.slug', 'ceramic-mug')
        );
    }

    public function test_product_detail_page_returns_404_for_inactive_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'is_active' => false,
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(404);
    }
}
