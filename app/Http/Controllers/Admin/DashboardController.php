<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard statistics.
     */
    public function index(): Response
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $inactiveProducts = Product::where('is_active', false)->count();

        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        $recentOrders = Order::with('items')
            ->latest('id')
            ->take(5)
            ->get();

        $recentProducts = Product::latest('id')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $inactiveProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'total_revenue' => (float) $totalRevenue,
            ],
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
        ]);
    }
}
