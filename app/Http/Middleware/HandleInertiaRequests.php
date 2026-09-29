<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'avatar_url' => $request->user()->avatar_url,
                ] : null,
            ],
            'store' => fn () => StoreSetting::first() ?? [
                'name' => 'EcoStore',
                'tagline' => 'Produk Kriya & Gaya Hidup Ramah Lingkungan',
                'logo_url' => file_exists(public_path('assets/img/logo.png')) ? asset('assets/img/logo.png') : null,
                'phone' => '08985454555',
                'clean_phone' => '628985454555',
                'email' => 'kontak@ecostore.com',
                'address' => 'Jl. Kerajinan No. 12, Sleman, D.I. Yogyakarta 55281',
                'description' => 'EcoStore bermula dari sebuah bengkel kriya keluarga yang peduli dengan keberlanjutan lingkungan. Kami percaya bahwa produk rumah tangga dan dekorasi tidak harus mengorbankan kelestarian alam.',
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
