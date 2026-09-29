<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_place_order_and_receive_whatsapp_url(): void
    {
        $product = Product::factory()->create([
            'name' => 'Kopi Robusta Kriya',
            'price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/orders', [
            'customer_name' => 'Ahmad Pembeli',
            'customer_phone' => '081234567890',
            'customer_email' => 'ahmad@example.com',
            'customer_address' => 'Jl. Kebon Jeruk No. 5, Jakarta Barat',
            'notes' => 'Tolong kirim sebelum jam 3 sore',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order' => ['id', 'order_number', 'customer_name', 'total_amount'],
            'whatsapp_url',
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Ahmad Pembeli',
            'customer_phone' => '081234567890',
            'total_amount' => 100000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
            'subtotal' => 100000,
        ]);

        $data = $response->json();
        $this->assertStringContainsString('628985454555', $data['whatsapp_url']);
    }

    public function test_admin_can_view_orders_and_update_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'INV-20260929-9999',
            'customer_name' => 'Siti Aminah',
            'customer_phone' => '0899999999',
            'customer_address' => 'Bandung, Jawa Barat',
            'total_amount' => 75000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/orders');
        $response->assertStatus(200);

        $updateResponse = $this->actingAs($admin)->put("/admin/orders/{$order->id}/status", [
            'status' => 'shipped',
            'shipping_cost' => 15000,
            'courier' => 'J&T Express',
            'tracking_number' => 'JT9988776655',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'shipping_cost' => 15000,
            'courier' => 'J&T Express',
            'tracking_number' => 'JT9988776655',
        ]);
    }

    public function test_guest_can_access_tracking_page_and_track_order(): void
    {
        $order = Order::create([
            'order_number' => 'INV-20260929-8888',
            'customer_name' => 'Dewi Sartika',
            'customer_phone' => '08123456789',
            'customer_address' => 'Yogyakarta',
            'total_amount' => 120000,
            'shipping_cost' => 10000,
            'courier' => 'SiCepat',
            'tracking_number' => 'SCP12345',
            'status' => 'shipped',
        ]);

        // General track page
        $response = $this->get('/orders/track');
        $response->assertStatus(200);

        // Track with order number in route parameter
        $paramResponse = $this->get("/orders/track/{$order->order_number}");
        $paramResponse->assertStatus(200);

        // Track with query string search
        $queryResponse = $this->get("/orders/track?q={$order->order_number}");
        $queryResponse->assertStatus(200);
    }

    public function test_guest_can_download_order_invoice_pdf(): void
    {
        $order = Order::create([
            'order_number' => 'INV-20260929-7777',
            'customer_name' => 'Rahmat Hidayat',
            'customer_phone' => '081234567890',
            'customer_address' => 'Semarang',
            'total_amount' => 95000,
            'shipping_cost' => 12000,
            'status' => 'processing',
        ]);

        $order->items()->create([
            'product_name' => 'Sample Item',
            'price' => 95000,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        $response = $this->get("/orders/{$order->order_number}/invoice");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_order_invoice_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'INV-20260929-0001',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '0812345678',
            'customer_address' => 'Surabaya',
            'total_amount' => 45000,
            'status' => 'pending',
        ]);

        $order->items()->create([
            'product_name' => 'Sample Bamboo Lamp',
            'price' => 45000,
            'quantity' => 1,
            'subtotal' => 45000,
        ]);

        $response = $this->actingAs($admin)->get("/admin/orders/{$order->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_delete_cancelled_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'INV-20260929-9998',
            'customer_name' => 'Pemesan Batal',
            'customer_phone' => '0812345678',
            'customer_address' => 'Jakarta Barat',
            'total_amount' => 50000,
            'status' => 'cancelled',
        ]);

        $order->items()->create([
            'product_name' => 'Sample Product',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/orders/{$order->id}");

        $response->assertRedirect(route('admin.orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
    }

    public function test_admin_cannot_delete_non_cancelled_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'INV-20260929-9997',
            'customer_name' => 'Pemesan Aktif',
            'customer_phone' => '0812345678',
            'customer_address' => 'Jakarta Pusat',
            'total_amount' => 75000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/orders/{$order->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_guest_cannot_order_inactive_product(): void
    {
        $inactiveProduct = Product::factory()->create([
            'name' => 'Produk Nonaktif',
            'price' => 100000,
            'is_active' => false,
        ]);

        $response = $this->postJson('/orders', [
            'customer_name' => 'Budi Pembeli',
            'customer_phone' => '081234567891',
            'customer_address' => 'Jl. Merdeka No. 1',
            'items' => [
                [
                    'product_id' => $inactiveProduct->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Tidak ada produk valid yang dipesan.',
        ]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_cannot_order_more_than_available_stock(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produk Terbatas',
            'price' => 50000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->postJson('/orders', [
            'customer_name' => 'Citra Pembeli',
            'customer_phone' => '081234567892',
            'customer_address' => 'Jl. Diponegoro No. 10',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => "Stok produk 'Produk Terbatas' tidak mencukupi (sisa: 2 unit).",
        ]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_placement_deducts_stock(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produk Tersedia',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson('/orders', [
            'customer_name' => 'Dedi Pembeli',
            'customer_phone' => '081234567893',
            'customer_address' => 'Jl. Sudirman No. 20',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['customer_name' => 'Dedi Pembeli']);
        $this->assertEquals(7, $product->fresh()->stock);
    }
}
