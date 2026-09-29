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
            'status' => 'confirmed',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
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
}
