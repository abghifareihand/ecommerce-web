<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gaya Hidup & Minum',
                'slug' => 'gaya-hidup-minum',
                'description' => 'Tumbler insulasi, botol minum stainless, dan perlengkapan minum ramah lingkungan.',
                'order' => 1,
            ],
            [
                'name' => 'Tas & Aksesoris',
                'slug' => 'tas-aksesoris',
                'description' => 'Totebag kanvas organik, dompet kartu kulit nabati, dan pouch ramah alam.',
                'order' => 2,
            ],
            [
                'name' => 'Dekorasi & Rumah',
                'slug' => 'dekorasi-rumah',
                'description' => 'Lilin kedelai aromaterapi, lampu bambu, dan kerajinan dekorasi estetik.',
                'order' => 3,
            ],
            [
                'name' => 'Alat Makan & Dapur',
                'slug' => 'alat-makan-dapur',
                'description' => 'Set sendok garpu bambu, mangkok kayu kelapa, dan sedotan ramah lingkungan.',
                'order' => 4,
            ],
            [
                'name' => 'Pakaian & Tekstil Organik',
                'slug' => 'pakaian-tekstil-organik',
                'description' => 'Kaos katun bambu, syal tenun alami, dan celemek linen ramah lingkungan.',
                'order' => 5,
            ],
            [
                'name' => 'Perawatan Diri & Spa',
                'slug' => 'perawatan-diri-spa',
                'description' => 'Sabun batang vegan alami, sikat gigi bambu, dan spons mandi serat loofah.',
                'order' => 6,
            ],
            [
                'name' => 'Alat Tulis & Kertas Daur Ulang',
                'slug' => 'alat-tulis-kertas-daur-ulang',
                'description' => 'Buku catatan kertas daur ulang, pulpen bambu, dan pouch pensil kain serat.',
                'order' => 7,
            ],
            [
                'name' => 'Tanaman & Kebun Rumah',
                'slug' => 'tanaman-kebun-rumah',
                'description' => 'Pot sabut kelapa organik, set alat kebun mini, dan benih tanaman herbal.',
                'order' => 8,
            ],
            [
                'name' => 'Mainan Edukatif Kayu',
                'slug' => 'mainan-edukatif-kayu',
                'description' => 'Balok susun kayu pinus, puzzle hewan non-toksik, dan miniatur ramah anak.',
                'order' => 9,
            ],
            [
                'name' => 'Lilin Aromaterapi & Diffuser',
                'slug' => 'lilin-aromaterapi-diffuser',
                'description' => 'Lilin minyak kelapa sawit lestari, minyak esensial lokal, dan reed diffuser bambu.',
                'order' => 10,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
