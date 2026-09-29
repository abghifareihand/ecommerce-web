<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
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
        $categorySlug = $request->query('category');
        $sort = $request->query('sort', 'latest');

        $query = Product::query()
            ->with('category')
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
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

        $categories = Category::query()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('order', 'asc')
            ->get();

        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->latest('id')
            ->get();

        return Inertia::render('Guest/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $search ?? '',
                'category' => $categorySlug ?? '',
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

        $product->load('category');

        $relatedProducts = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->latest('id')
            ->take(4)
            ->get();

        $store = StoreSetting::first();

        return Inertia::render('Guest/Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'store' => $store,
        ]);
    }
}
