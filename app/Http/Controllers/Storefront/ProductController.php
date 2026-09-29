<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a rich ecommerce catalog of active products.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $sort = $request->query('sort', 'latest');

        $query = Product::query()
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'oldest' => $query->oldest('id'),
            'latest' => $query->latest('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->latest('id')
            ->get();

        return Inertia::render('Guest/Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $search ?? '',
                'sort' => $sort,
            ],
            'totalCount' => Product::where('is_active', true)->count(),
            'banners' => $banners,
        ]);
    }

    /**
     * Display the specified product detail page with ordering options.
     */
    public function show(Product $product): Response
    {
        if (! $product->is_active) {
            abort(404, 'Product not found or inactive.');
        }

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->latest('id')
            ->take(4)
            ->get();

        return Inertia::render('Guest/Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
