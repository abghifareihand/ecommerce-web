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

        if ($status && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            $query->where('status', $status);
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
                'confirmed' => Order::where('status', 'confirmed')->count(),
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

        $invoiceUrl = route('admin.orders.pdf', $order->id);
        $storeName = StoreSetting::first()?->name ?? 'EcoStore';

        $customerWaMessage = "Halo Kak {$order->customer_name}, terima kasih telah berbelanja di {$storeName}!\n\n".
            "Pesanan Anda dengan nomor *#{$order->order_number}* saat ini berstatus: *".strtoupper($order->status)."*.\n\n".
            "Berikut detail invoice resmi Anda yang dapat diunduh:\n".
            "{$invoiceUrl}\n\n".
            'Terima kasih atas kepercayaan Anda!';

        $encodedMsg = rawurlencode($customerWaMessage);
        $customerWaUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text={$encodedMsg}";

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'customerWaUrl' => $customerWaUrl,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
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
