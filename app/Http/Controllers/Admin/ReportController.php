<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display a listing of sales reports and summaries.
     */
    public function index(Request $request): Response
    {
        $filters = $this->resolveFilters($request);
        $query = $this->buildQuery($filters);

        // Calculate Summary Metrics
        $metricsQuery = clone $query;
        $ordersForMetrics = $metricsQuery->with('items')->get();

        $validOrders = $ordersForMetrics->where('status', '!=', 'cancelled');

        $totalRevenue = (float) $validOrders->sum('total_amount');
        $totalShipping = (float) $validOrders->sum('shipping_cost');
        $grandTotalRevenue = $totalRevenue + $totalShipping;
        $totalOrdersCount = $ordersForMetrics->count();
        $completedOrdersCount = $ordersForMetrics->where('status', 'completed')->count();
        $cancelledOrdersCount = $ordersForMetrics->where('status', 'cancelled')->count();
        $activeOrdersCount = $ordersForMetrics->whereIn('status', ['pending', 'payment_pending', 'processing', 'shipped'])->count();

        $totalItemsSold = $validOrders->flatMap(fn ($order) => $order->items)->sum('quantity');

        $validOrdersCount = $validOrders->count();
        $averageOrderValue = $validOrdersCount > 0 ? $grandTotalRevenue / $validOrdersCount : 0;

        // Paginated records for table
        $orders = (clone $query)->with('items')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reports/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_shipping' => $totalShipping,
                'grand_total_revenue' => $grandTotalRevenue,
                'total_orders' => $totalOrdersCount,
                'completed_orders' => $completedOrdersCount,
                'cancelled_orders' => $cancelledOrdersCount,
                'active_orders' => $activeOrdersCount,
                'total_items_sold' => (int) $totalItemsSold,
                'average_order_value' => (float) round($averageOrderValue, 2),
            ],
        ]);
    }

    /**
     * Export sales report to professionally styled Excel (.xlsx) format.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $orders = $this->buildQuery($filters)->with('items')->latest('created_at')->get();
        $store = StoreSetting::first();
        $storeName = $store->name ?? 'EcoStore';

        $filename = 'laporan-penjualan-' . $filters['start_date'] . '-ke-' . $filters['end_date'] . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Penjualan');

        // Show gridlines in Excel
        $sheet->setShowGridLines(true);

        // --- 1. TITLE & METADATA SECTION ---
        $sheet->setCellValue('A1', strtoupper($storeName) . ' - LAPORAN REKAP PENJUALAN');
        $sheet->mergeCells('A1:Q1');
        $sheet->getStyle('A1')->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('065F46');
        $sheet->getRowDimension(1)->setRowHeight(28);

        $statusLabel = match ($filters['status']) {
            'completed' => 'Selesai',
            'shipped' => 'Sedang Dikirim',
            'processing' => 'Diproses & Dikemas',
            'payment_pending' => 'Menunggu Pembayaran',
            'pending' => 'Pending (Cek Ongkir)',
            'cancelled' => 'Dibatalkan',
            default => 'Semua Status Transaksi',
        };

        $sheet->setCellValue('A2', "Periode Data: {$filters['start_date']} s/d {$filters['end_date']}   |   Status: {$statusLabel}   |   Waktu Unduh: " . Carbon::now()->format('d-m-Y H:i:s'));
        $sheet->mergeCells('A2:Q2');
        $sheet->getStyle('A2')->getFont()->setName('Arial')->setSize(10)->setItalic(true)->getColor()->setRGB('475569');
        $sheet->getRowDimension(2)->setRowHeight(18);

        // --- 2. SUMMARY KPI STATS SECTION ---
        $validOrders = $orders->where('status', '!=', 'cancelled');
        $totalOmset = $validOrders->sum('total_amount') + $validOrders->sum('shipping_cost');
        $totalItems = $validOrders->flatMap(fn ($o) => $o->items)->sum('quantity');
        $avgOrder = $validOrders->count() > 0 ? $totalOmset / $validOrders->count() : 0;

        // KPI Box styling
        $sheet->setCellValue('B4', 'TOTAL OMSET BERSIH');
        $sheet->setCellValue('B5', (float) $totalOmset);
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $sheet->setCellValue('E4', 'TOTAL PESANAN');
        $sheet->setCellValue('E5', $orders->count() . ' Transaksi');

        $sheet->setCellValue('H4', 'PRODUK TERJUAL');
        $sheet->setCellValue('H5', $totalItems . ' Pcs');

        $sheet->setCellValue('K4', 'RATA-RATA ORDER');
        $sheet->setCellValue('K5', (float) $avgOrder);
        $sheet->getStyle('K5')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $kpiHeaderStyle = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '047857']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ];
        $kpiValStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]],
        ];

        foreach (['B', 'E', 'H', 'K'] as $col) {
            $sheet->mergeCells("{$col}4:" . chr(ord($col) + 1) . '4');
            $sheet->mergeCells("{$col}5:" . chr(ord($col) + 1) . '5');
            $sheet->getStyle("{$col}4:" . chr(ord($col) + 1) . '4')->applyFromArray($kpiHeaderStyle);
            $sheet->getStyle("{$col}5:" . chr(ord($col) + 1) . '5')->applyFromArray($kpiValStyle);
        }

        // --- 3. DATA TABLE HEADER ---
        $headerRow = 7;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        $columns = [
            'A' => 'No',
            'B' => 'No. Invoice',
            'C' => 'Tanggal',
            'D' => 'Waktu',
            'E' => 'Nama Pelanggan',
            'F' => 'No. WhatsApp',
            'G' => 'Email',
            'H' => 'Alamat Pengiriman',
            'I' => 'Status Pesanan',
            'J' => 'Rincian Produk & Qty',
            'K' => 'Total Qty',
            'L' => 'Subtotal Produk',
            'M' => 'Ongkos Kirim',
            'N' => 'Total Bayar',
            'O' => 'Kurir',
            'P' => 'No. Resi',
            'Q' => 'Catatan',
        ];

        foreach ($columns as $col => $title) {
            $sheet->setCellValue("{$col}{$headerRow}", $title);
        }

        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Emerald 600
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '047857'],
                ],
            ],
        ];
        $sheet->getStyle("A{$headerRow}:Q{$headerRow}")->applyFromArray($tableHeaderStyle);

        // --- 4. DATA ROWS ---
        $row = $headerRow + 1;
        $no = 1;

        foreach ($orders as $order) {
            $itemsText = $order->items->map(function ($item) {
                return "{$item->product_name} (x{$item->quantity})";
            })->implode("\n");

            $totalQty = $order->items->sum('quantity');

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValueExplicit("B{$row}", $order->order_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $order->created_at ? $order->created_at->format('Y-m-d') : '-');
            $sheet->setCellValue("D{$row}", $order->created_at ? $order->created_at->format('H:i') : '-');
            $sheet->setCellValue("E{$row}", $order->customer_name);
            $sheet->setCellValueExplicit("F{$row}", (string) $order->customer_phone, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", $order->customer_email ?: '-');
            $sheet->setCellValue("H{$row}", $order->customer_address);
            $sheet->setCellValue("I{$row}", $order->status_label);
            $sheet->setCellValue("J{$row}", $itemsText);
            $sheet->getStyle("J{$row}")->getAlignment()->setWrapText(true);
            $sheet->setCellValue("K{$row}", $totalQty);
            $sheet->setCellValue("L{$row}", (float) $order->total_amount);
            $sheet->setCellValue("M{$row}", (float) ($order->shipping_cost ?? 0));
            $sheet->setCellValue("N{$row}", (float) $order->grand_total);
            $sheet->setCellValue("O{$row}", $order->courier ?: '-');
            $sheet->setCellValueExplicit("P{$row}", $order->tracking_number ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("Q{$row}", $order->notes ?: '-');

            // Format Currency
            $sheet->getStyle("L{$row}:N{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Zebra background & borders
            $rowBg = ($row % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $rowBg],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Specific alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(false);
            $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O{$row}:P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height
            $row++;
        }

        // --- 5. SUMMARY FOOTER ROW ---
        $summaryRow = $row;
        $sheet->setCellValue("A{$summaryRow}", 'TOTAL AKUMULASI:');
        $sheet->mergeCells("A{$summaryRow}:K{$summaryRow}");

        $dataStartRow = $headerRow + 1;
        $dataEndRow = $row - 1;

        if ($dataEndRow >= $dataStartRow) {
            $sheet->setCellValue("L{$summaryRow}", "=SUM(L{$dataStartRow}:L{$dataEndRow})");
            $sheet->setCellValue("M{$summaryRow}", "=SUM(M{$dataStartRow}:M{$dataEndRow})");
            $sheet->setCellValue("N{$summaryRow}", "=SUM(N{$dataStartRow}:N{$dataEndRow})");
        } else {
            $sheet->setCellValue("L{$summaryRow}", 0);
            $sheet->setCellValue("M{$summaryRow}", 0);
            $sheet->setCellValue("N{$summaryRow}", 0);
        }

        $sheet->getStyle("L{$summaryRow}:N{$summaryRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

        $footerStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '065F46']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D1FAE5'], // Emerald 100
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '059669']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '059669']],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A{$summaryRow}:Q{$summaryRow}")->applyFromArray($footerStyle);
        $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($summaryRow)->setRowHeight(24);

        // --- 6. AUTO-FIT COLUMN WIDTHS ---
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set minimum width for text-heavy columns
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(35);

        // --- 7. STREAM EXCEL RESPONSE ---
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }

    /**
     * Resolve date and filter parameters with presets support.
     */
    private function resolveFilters(Request $request): array
    {
        $preset = $request->query('preset', 'this_month');
        $now = Carbon::now();

        $startDate = null;
        $endDate = null;

        switch ($preset) {
            case 'today':
                $startDate = $now->copy()->startOfDay()->format('Y-m-d');
                $endDate = $now->copy()->endOfDay()->format('Y-m-d');
                break;
            case 'last_7_days':
                $startDate = $now->copy()->subDays(6)->startOfDay()->format('Y-m-d');
                $endDate = $now->copy()->endOfDay()->format('Y-m-d');
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear()->format('Y-m-d');
                $endDate = $now->copy()->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
            default:
                $preset = 'custom';
                $startDate = $request->query('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
                $endDate = $request->query('end_date', $now->copy()->endOfDay()->format('Y-m-d'));
                break;
        }

        return [
            'preset' => $preset,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $request->query('status', 'all'),
        ];
    }

    /**
     * Build the query for orders filtering.
     */
    private function buildQuery(array $filters)
    {
        $query = Order::query();

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
