<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Order::query()->with('items')->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['pending', 'payment_pending', 'processing', 'confirmed', 'shipped', 'completed', 'cancelled'])) {
            if ($status === 'processing') {
                $query->whereIn('status', ['processing', 'confirmed']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
            'statusCounts' => [
                'all' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'payment_pending' => Order::where('status', 'payment_pending')->count(),
                'processing' => Order::whereIn('status', ['processing', 'confirmed'])->count(),
                'shipped' => Order::where('status', 'shipped')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ],
        ]);
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order): Response
    {
        $order->load('items.product');

        // Clean customer phone number for WhatsApp (replace leading 0 with 62)
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $store = StoreSetting::first();
        $storeName = $store?->name ?? 'EcoStore';
        $trackingUrl = url("/orders/track/{$order->order_number}");

        // 1. WhatsApp Template: Rincian Tagihan & Ongkir
        $subtotalFmt = number_format($order->total_amount, 0, ',', '.');
        $shippingFmt = number_format($order->shipping_cost, 0, ',', '.');
        $grandTotalFmt = number_format($order->grand_total, 0, ',', '.');
        $courierName = $order->courier ?: 'Kurir Rekanan';

        $billingMessage = "Halo Kak {$order->customer_name}, berikut rincian tagihan pesanan Anda di {$storeName} (*#{$order->order_number}*):\n\n".
            "📦 *Rincian Biaya:*\n".
            "- Subtotal Barang : Rp {$subtotalFmt}\n".
            "- Ongkos Kirim ({$courierName}) : Rp {$shippingFmt}\n".
            "- *Total Pembayaran:* *Rp {$grandTotalFmt}*\n\n".
            ($store?->bank_account ? "💳 *Silakan Transfer ke Rekening Toko:*\n{$store->bank_account}\n\n" : '').
            "🔍 *Lacak Status Pesanan:* {$trackingUrl}\n\n".
            'Mohon kirimkan bukti transfer ke WhatsApp ini jika sudah melakukan pembayaran ya Kak. Terima kasih!';

        $waBillingUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=".rawurlencode($billingMessage);

        // 2. WhatsApp Template: Informasi Resi & Pengiriman
        $trackingNum = $order->tracking_number ?: '-';
        $shippingMessage = "Halo Kak {$order->customer_name}, paket pesanan Anda (*#{$order->order_number}*) dari {$storeName} sudah diserahkan ke jasa kurir!\n\n".
            "🚚 *Ekspedisi:* {$courierName}\n".
            "🔖 *No. Resi:* *{$trackingNum}*\n\n".
            "🔍 *Lacak Status Pesanan Anda:* {$trackingUrl}\n\n".
            'Terima kasih atas pesanannya! Ditunggu barangnya sampai dengan selamat ya Kak.';

        $waShippingUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=".rawurlencode($shippingMessage);

        // 3. Generic status update message
        $genericMessage = "Halo Kak {$order->customer_name}, status pesanan Anda *#{$order->order_number}* di {$storeName} saat ini: *".strtoupper($order->status_label)."*.\n\n".
            "🔍 Lacak Pesanan: {$trackingUrl}\n".
            'Terima kasih atas kepercayaan Anda!';
        $customerWaUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=".rawurlencode($genericMessage);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'customerWaUrl' => $customerWaUrl,
            'waBillingUrl' => $waBillingUrl,
            'waShippingUrl' => $waShippingUrl,
            'trackingUrl' => $trackingUrl,
            'store' => $store,
        ]);
    }

    /**
     * Update order status, shipping cost, courier, and tracking number.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,payment_pending,processing,confirmed,shipped,completed,cancelled'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'courier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $validated['status'] === 'confirmed' ? 'processing' : $validated['status'];

        $order->update([
            'status' => $status,
            'shipping_cost' => $validated['shipping_cost'] ?? 0,
            'courier' => $validated['courier'] ?? null,
            'tracking_number' => $validated['tracking_number'] ?? null,
        ]);

        return back()->with('success', "Informasi dan status pesanan #{$order->order_number} berhasil diperbarui.");
    }

    /**
     * Download the order invoice as a PDF.
     */
    public function downloadPdf(Order $order): HttpResponse
    {
        $order->load('items');

        $pdf = Pdf::loadView('invoices.order-pdf', [
            'order' => $order,
        ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    /**
     * Stream the order invoice PDF in browser.
     */
    public function streamPdf(Order $order): HttpResponse
    {
        $order->load('items');

        $pdf = Pdf::loadView('invoices.order-pdf', [
            'order' => $order,
        ]);

        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }

    /**
     * Delete a cancelled order and invoice.
     */
    public function destroy(Order $order): RedirectResponse
    {
        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Hanya pesanan berstatus Dibatalkan yang dapat dihapus.');
        }

        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', "Pesanan #{$orderNumber} berhasil dihapus.");
    }
}
