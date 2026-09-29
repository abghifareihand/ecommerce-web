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
        ];

        $allowedSlugs = array_column($categories, 'slug');

        // Delete categories that are not in the top 5
        Category::whereNotIn('slug', $allowedSlugs)->delete();

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
