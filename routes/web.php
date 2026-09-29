<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\StoreSettingController as AdminStoreSettingController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderController as StorefrontOrderController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Guest Shopping & Catalog Routes
|--------------------------------------------------------------------------
*/
Route::get('/products', [StorefrontProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [StorefrontProductController::class, 'show'])->name('products.show');
Route::get('/cart', fn () => Inertia::render('Guest/Cart/Index'))->name('cart.index');
Route::get('/checkout', fn () => Inertia::render('Guest/Checkout/Index'))->name('checkout.index');
Route::post('/orders', [StorefrontOrderController::class, 'store'])->name('orders.store');

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Products CRUD
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])->name('index');
            Route::get('/create', [AdminProductController::class, 'create'])->name('create');
            Route::post('/', [AdminProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [AdminProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy');
        });

        // Orders Management & PDF Invoices
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('status');
            Route::delete('/{order}', [AdminOrderController::class, 'destroy'])->name('destroy');
            Route::get('/{order}/pdf', [AdminOrderController::class, 'downloadPdf'])->name('pdf');
            Route::get('/{order}/pdf/stream', [AdminOrderController::class, 'streamPdf'])->name('pdf.stream');
        });

        // Banners Management
        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/', [AdminBannerController::class, 'index'])->name('index');
            Route::get('/create', [AdminBannerController::class, 'create'])->name('create');
            Route::post('/', [AdminBannerController::class, 'store'])->name('store');
            Route::get('/{banner}/edit', [AdminBannerController::class, 'edit'])->name('edit');
            Route::put('/{banner}', [AdminBannerController::class, 'update'])->name('update');
            Route::delete('/{banner}', [AdminBannerController::class, 'destroy'])->name('destroy');
            Route::patch('/{banner}/toggle', [AdminBannerController::class, 'toggle'])->name('toggle');
        });

        // Admin Profile Management
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [AdminProfileController::class, 'edit'])->name('edit');
            Route::post('/', [AdminProfileController::class, 'update'])->name('update');
            Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');
            Route::delete('/avatar', [AdminProfileController::class, 'destroyAvatar'])->name('avatar.destroy');
        });

        // Store Settings Management
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('/', [AdminStoreSettingController::class, 'edit'])->name('edit');
            Route::match(['put', 'post'], '/', [AdminStoreSettingController::class, 'update'])->name('update');
            Route::delete('/logo', [AdminStoreSettingController::class, 'destroyLogo'])->name('logo.destroy');
        });
    });
});
