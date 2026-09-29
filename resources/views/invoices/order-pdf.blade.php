@php
    $store = \App\Models\StoreSetting::first();
    $storeName = $store?->name ?? 'EcoStore';
    $storeTagline = $store?->tagline ?? 'Toko Online UMKM Pilihan Berkualitas';
    $storePhone = $store?->phone ?? '08985454555';
    $storeAddress = $store?->address ?? '';
    $logoPath = null;
    if ($store?->logo && file_exists(public_path('storage/' . $store->logo))) {
        $logoPath = public_path('storage/' . $store->logo);
    } elseif (file_exists(public_path('assets/img/logo.png'))) {
        $logoPath = public_path('assets/img/logo.png');
    } elseif (file_exists(public_path('assets/img/logo.jpg'))) {
        $logoPath = public_path('assets/img/logo.jpg');
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            font-size: 13px;
            line-height: 1.5;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #16a34a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .brand-container {
            float: left;
            display: flex;
            align-items: center;
        }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #14532d;
        }
        .brand span {
            color: #16a34a;
        }
        .invoice-title {
            float: right;
            text-align: right;
        }
        .invoice-title h2 {
            margin: 0;
            color: #0f172a;
            font-size: 20px;
        }
        .clear {
            clear: both;
        }
        .details-box {
            margin-bottom: 25px;
        }
        .customer-info {
            float: left;
            width: 55%;
        }
        .order-meta {
            float: right;
            width: 40%;
            text-align: right;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .total-box {
            float: right;
            width: 40%;
            margin-bottom: 30px;
        }
        .total-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-box td {
            padding: 6px 12px;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            color: #16a34a;
            border-top: 2px solid #cbd5e1;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending { background-color: #fef3c7; color: #b45309; }
        .badge-payment_pending { background-color: #ffedd5; color: #c2410c; }
        .badge-processing { background-color: #e0f2fe; color: #0369a1; }
        .badge-confirmed { background-color: #e0f2fe; color: #0369a1; }
        .badge-shipped { background-color: #f3e8ff; color: #7e22ce; }
        .badge-completed { background-color: #dcfce7; color: #15803d; }
        .badge-cancelled { background-color: #fee2e2; color: #b91c1c; }
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="invoice-title">
            <h2>INVOICE PESANAN</h2>
            <div style="margin-top: 4px;">
                <span class="badge badge-{{ $order->status }}">Status: {{ strtoupper($order->status_label ?? $order->status) }}</span>
            </div>
        </div>
        <table style="border: none; border-collapse: collapse;">
            <tr>
                @if($logoPath)
                    <td style="vertical-align: middle; padding-right: 12px; border: none;">
                        <img src="{{ $logoPath }}" style="height: 44px; max-width: 100px; object-fit: contain;" alt="Logo">
                    </td>
                @endif
                <td style="vertical-align: middle; border: none;">
                    <div class="brand">{{ $storeName }}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $storeTagline }}</div>
                    @if($storeAddress)
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">{{ $storeAddress }}</div>
                    @endif
                </td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    <div class="details-box">
        <div class="customer-info">
            <strong style="color: #0f172a;">Diterbitkan Untuk:</strong><br>
            <strong>{{ $order->customer_name }}</strong><br>
            No. WhatsApp: {{ $order->customer_phone }}<br>
            @if($order->customer_email)
                Email: {{ $order->customer_email }}<br>
            @endif
            Alamat: {{ $order->customer_address }}<br>
            @if($order->notes)
                <div style="margin-top: 4px; font-style: italic; color: #64748b;">
                    Catatan: {{ $order->notes }}
                </div>
            @endif
        </div>

        <div class="order-meta">
            <strong>No. Invoice:</strong> {{ $order->order_number }}<br>
            <strong>Tanggal:</strong> {{ $order->created_at->format('d/m/Y H:i') }} WIB<br>
            <strong>Kontak Toko:</strong> {{ $storePhone }}<br>
            @if($order->courier)
                <strong>Ekspedisi / Kurir:</strong> {{ $order->courier }}<br>
            @endif
            @if($order->tracking_number)
                <strong>No. Resi Pengiriman:</strong> <span style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $order->tracking_number }}</span><br>
            @endif
        </div>
        <div class="clear"></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Nama Produk</th>
                <th class="text-right" style="width: 15%;">Harga</th>
                <th class="text-right" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <table>
            <tr>
                <td>Subtotal Belanja</td>
                <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ongkos Kirim</td>
                <td class="text-right">
                    @if($order->shipping_cost > 0)
                        Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                    @elseif($order->status === 'pending')
                        <span style="color: #b45309; font-size: 11px;">(Menunggu konfirmasi)</span>
                    @else
                        <span style="color: #16a34a; font-size: 11px;">Rp 0 (Gratis)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="grand-total">Total Tagihan</td>
                <td class="text-right grand-total">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>

    @if($store?->bank_account)
        <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
            <strong style="color: #0f172a; font-size: 12px;">Rekening Pembayaran Toko:</strong>
            <div style="margin-top: 4px; font-size: 12px; color: #334155; white-space: pre-line;">{!! nl2br(e($store->bank_account)) !!}</div>
            <div style="margin-top: 6px; font-size: 11px; color: #64748b;">
                * Kirimkan bukti transfer ke WhatsApp Admin di <strong>{{ $storePhone }}</strong> dengan mencantumkan nomor invoice <strong>{{ $order->order_number }}</strong>.
            </div>
        </div>
    @endif

    <div style="background-color: #f0fdf4; border: 1px dashed #86efac; border-radius: 6px; padding: 12px; margin-bottom: 25px;">
        <strong style="color: #166534;">Lacak Status Pesanan:</strong>
        <p style="margin: 4px 0 0 0; font-size: 12px; color: #14532d;">
            Pelanggan dapat memantau perkembangan pesanan secara real-time kapan saja melalui tautan lacak pesanan toko kami di: <strong>{{ url('/orders/track/' . $order->order_number) }}</strong>
        </p>
    </div>

    <div class="footer">
        Terima kasih telah berbelanja dan mendukung produk UMKM lokal!<br>
        {{ $storeName }} &copy; {{ date('Y') }}
    </div>
</body>
</html>
