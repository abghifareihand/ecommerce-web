<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StoreSetting::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'EcoStore',
                'tagline' => 'Produk Kriya & Gaya Hidup Ramah Lingkungan',
                'logo' => 'assets/img/logo.png',
                'phone' => '08985454555',
                'email' => 'kontak@ecostore.com',
                'address' => 'Jl. Kerajinan No. 12, Sleman, D.I. Yogyakarta 55281',
                'bank_account' => "BCA: 123-456-7890 a/n EcoStore Indonesia\nMandiri: 987-654-3210 a/n EcoStore Indonesia",
                'description' => 'EcoStore bermula dari sebuah bengkel kriya keluarga yang peduli dengan keberlanjutan lingkungan. Kami percaya bahwa produk rumah tangga dan dekorasi tidak harus mengorbankan kelestarian alam.',
            ]
        );
    }
}
