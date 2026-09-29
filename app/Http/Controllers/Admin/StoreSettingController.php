<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingController extends Controller
{
    /**
     * Show the store setting edit form.
     */
    public function edit(): Response
    {
        $store = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'EcoStore',
                'tagline' => 'Produk Kriya & Gaya Hidup Ramah Lingkungan',
                'phone' => '08985454555',
                'email' => 'kontak@ecostore.com',
                'address' => 'Jl. Kerajinan No. 12, Sleman, D.I. Yogyakarta 55281',
                'description' => 'EcoStore bermula dari sebuah bengkel kriya keluarga yang peduli dengan keberlanjutan lingkungan.',
            ]
        );

        return Inertia::render('Admin/Store/Edit', [
            'store' => $store,
        ]);
    }

    /**
     * Update the store setting.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        $store = StoreSetting::firstOrCreate(['id' => 1]);

        $data = [
            'name' => $validated['name'],
            'tagline' => $validated['tagline'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('store', 'public');
        }

        $store->update($data);

        return back()->with('success', 'Informasi toko berhasil diperbarui.');
    }

    /**
     * Remove the store logo.
     */
    public function destroyLogo(): RedirectResponse
    {
        $store = StoreSetting::first();

        if ($store && $store->logo && Storage::disk('public')->exists($store->logo)) {
            Storage::disk('public')->delete($store->logo);
            $store->update(['logo' => null]);
        }

        return back()->with('success', 'Logo toko berhasil dihapus.');
    }
}
