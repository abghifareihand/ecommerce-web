<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logoPath = null;
        if (file_exists(public_path('assets/img/logo.png'))) {
            Storage::disk('public')->put('store/logo.png', file_get_contents(public_path('assets/img/logo.png')));
            $logoPath = 'store/logo.png';
        }

        StoreSetting::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'EcoStore',
                'tagline' => 'Produk Kriya & Gaya Hidup Ramah Lingkungan',
                'logo' => $logoPath,
                'phone' => '08985454555',
                'email' => 'kontak@ecostore.com',
                'address' => 'Jl. Kerajinan No. 12, Sleman, D.I. Yogyakarta 55281',
                'description' => 'EcoStore bermula dari sebuah bengkel kriya keluarga yang peduli dengan keberlanjutan lingkungan. Kami percaya bahwa produk rumah tangga dan dekorasi tidak harus mengorbankan kelestarian alam.',
            ]
        );
    }
}
