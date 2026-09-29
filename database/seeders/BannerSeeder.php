<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Spesial Kriya Lokal & Gaya Hidup Berkelanjutan',
                'subtitle' => 'Dukung ekonomi pengrajin Nusantara dengan produk ramah lingkungan pilihan.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&auto=format&fit=crop&q=80',
                'link_url' => '/products',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Koleksi Kerajinan Kayu & Bambu Alami',
                'subtitle' => 'Sentuhan estetika alami nan ramah bumi untuk hunian dan ruang kerja modern.',
                'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=1600&auto=format&fit=crop&q=80',
                'link_url' => '/products',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Mulai Gaya Hidup Zero Waste Bersama EcoStore',
                'subtitle' => 'Pilihan perlengkapan harian bebas plastik sekali pakai dengan diskon spesial.',
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=1600&auto=format&fit=crop&q=80',
                'link_url' => '/products',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($banners as $item) {
            Banner::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
