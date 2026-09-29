<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get('/admin/reports');
        $response->assertRedirect('/admin/login');

        $exportResponse = $this->get('/admin/reports/export');
        $exportResponse->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_reports_page_with_metrics(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create(['price' => 50000]);

        $order = Order::create([
            'order_number' => 'INV-20260929-0001',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Thamrin No. 1, Jakarta',
            'total_amount' => 100000,
            'shipping_cost' => 15000,
            'status' => 'completed',
            'created_at' => Carbon::now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 50000,
            'quantity' => 2,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports?preset=this_month');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Index')
            ->has('orders.data')
            ->has('metrics')
            ->where('metrics.total_orders', 1)
            ->where('metrics.completed_orders', 1)
            ->where('metrics.total_items_sold', 2)
        );
    }

    public function test_admin_can_filter_reports_by_status(): void
    {
        $admin = $this->createAdmin();

        Order::create([
            'order_number' => 'INV-20260929-0002',
            'customer_name' => 'Rina Wijaya',
            'customer_phone' => '081234567891',
            'customer_address' => 'Jl. Sudirman No. 2, Bandung',
            'total_amount' => 100000,
            'status' => 'completed',
            'created_at' => Carbon::now(),
        ]);

        Order::create([
            'order_number' => 'INV-20260929-0003',
            'customer_name' => 'Doni Siregar',
            'customer_phone' => '081234567892',
            'customer_address' => 'Jl. Gatot Subroto No. 3, Medan',
            'total_amount' => 75000,
            'status' => 'cancelled',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports?status=completed');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Index')
            ->where('orders.total', 1)
        );
    }

    public function test_admin_can_export_sales_report_to_excel(): void
    {
        $admin = $this->createAdmin();

        $product = Product::factory()->create(['name' => 'Tas Tenun Organik', 'price' => 85000]);

        $order = Order::create([
            'order_number' => 'INV-20260929-9999',
            'customer_name' => 'Budi Sudarsono',
            'customer_phone' => '081298765432',
            'customer_address' => 'Jl. Merdeka No. 10, Bandung',
            'total_amount' => 85000,
            'shipping_cost' => 12000,
            'status' => 'completed',
            'created_at' => Carbon::now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 85000,
            'quantity' => 1,
            'subtotal' => 85000,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/export?preset=this_month');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $content = $response->streamedContent();
        // Valid zip/xlsx file starts with PK magic bytes
        $this->assertStringStartsWith("PK", $content);
    }
}
