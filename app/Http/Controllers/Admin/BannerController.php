<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    /**
     * Display a listing of the promo banners for admin.
     */
    public function index(): Response
    {
        $banners = Banner::query()
            ->orderBy('order', 'asc')
            ->latest('id')
            ->get();

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners,
        ]);
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create', [
            'nextOrder' => (Banner::max('order') ?? 0) + 1,
        ]);
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'image' => $imagePath,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $banner->image;
        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        $banner->update([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'image' => $imagePath,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    /**
     * Toggle the active status of a banner.
     */
    public function toggle(Banner $banner): RedirectResponse
    {
        $banner->update([
            'is_active' => ! $banner->is_active,
        ]);

        return redirect()->route('admin.banners.index')->with(
            'success',
            $banner->is_active ? 'Banner telah diaktifkan.' : 'Banner dinonaktifkan.'
        );
    }
}
