<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Store a guest order and return formatted WhatsApp URL.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $itemsToCreate = [];
            $whatsAppLines = [];

            // Load products securely from DB
            $productIds = collect($validated['items'])->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    continue;
                }

                $qty = (int) $item['quantity'];
                $subtotal = $product->price * $qty;
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];

                $formattedPrice = number_format($subtotal, 0, ',', '.');
                $whatsAppLines[] = "- {$product->name} ({$qty}x) : Rp {$formattedPrice}";
            }

            if (empty($itemsToCreate)) {
                return response()->json([
                    'message' => 'Tidak ada produk valid yang dipesan.',
                ], 422);
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            // Build clean WhatsApp message to store admin
            $store = StoreSetting::first();
            $storeName = $store?->name ?? 'EcoStore';
            $adminPhone = $store?->clean_phone ?? '628985454555';

            $itemsText = implode("\n", $whatsAppLines);
            $totalText = 'Rp '.number_format($totalAmount, 0, ',', '.');
            $notesText = ! empty($validated['notes']) ? "\n*Catatan:* ".$validated['notes'] : '';

            $message = "Halo Admin {$storeName}, saya mau konfirmasi pesanan baru:\n\n".
                "*No. Pesanan:* #{$order->order_number}\n".
                "*Nama:* {$order->customer_name}\n".
                "*No. WA:* {$order->customer_phone}\n".
                "*Alamat:* {$order->customer_address}{$notesText}\n\n".
                "*Rincian Pesanan:*\n".
                "{$itemsText}\n\n".
                "*Total Belanja:* *{$totalText}*\n\n".
                'Mohon info nomor rekening pembayarannya ya min. Terima kasih!';

            $encodedText = rawurlencode($message);
            $whatsAppUrl = "https://api.whatsapp.com/send?phone={$adminPhone}&text={$encodedText}";

            return response()->json([
                'success' => true,
                'order' => $order,
                'message_text' => $message,
                'whatsapp_url' => $whatsAppUrl,
            ]);
        });
    }
}
