<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $products = [
            // --- 1. GAYA HIDUP & MINUM ---
            [
                'category_slug' => 'gaya-hidup-minum',
                'name' => 'Tumbler Bambu Stainless Steel 500ml',
                'description' => 'Tumbler insulasi ganda berlapis bambu alami yang menjaga suhu minuman dingin hingga 24 jam atau panas hingga 12 jam. Bebas BPA dan ramah lingkungan.',
                'price' => 95000,
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'gaya-hidup-minum',
                'name' => 'Cangkir Keramik Buatan Tangan Rustic',
                'description' => 'Mug keramik stoneware buatan pengrajin lokal dengan glasir hijau botani yang aman untuk microwave dan mesin pencuci piring.',
                'price' => 90000,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'gaya-hidup-minum',
                'name' => 'Botol Minum Termos Stainless 750ml',
                'description' => 'Botol minum berkapasitas besar dari stainless steel food-grade 304 dengan pegangan silikon portabel yang praktis untuk olahraga dan bepergian.',
                'price' => 135000,
                'stock' => 28,
                'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'gaya-hidup-minum',
                'name' => 'Sedotan Kaca Reusable Set & Sikat Pembersih',
                'description' => 'Set 4 sedotan kaca borosilikat tahan panas dingin (lurus & bengkok) disertai sikat pembersih kawat dan kantong kain katun.',
                'price' => 85000,
                'stock' => 50,
                'image' => 'https://images.unsplash.com/photo-1596704017254-9b121068fb31?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'gaya-hidup-minum',
                'name' => 'Sikat Gigi Bambu Biodegradable (Paket 4 pcs)',
                'description' => 'Paket 4 batang sikat gigi dengan gagang bambu yang dapat terurai secara alami dan bulu sikat lembut charcoal bebas BPA.',
                'price' => 88000,
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1607613009820-a29f7bb81c04?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],

            // --- 2. TAS & AKSESORIS ---
            [
                'category_slug' => 'tas-aksesoris',
                'name' => 'Tas Totebag Kanvas Organik Eco',
                'description' => 'Totebag bahan katun kanvas organik 100% bersertifikat ramah lingkungan dengan jahitan ganda yang kokoh untuk belanja harian tanpa kantong plastik.',
                'price' => 85000,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1597484662317-9bd7bdda2907?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'tas-aksesoris',
                'name' => 'Dompet Kartu Kulit Nabati Ramah Lingkungan',
                'description' => 'Cardholder minimalis dari kulit samak nabati (vegetable-tanned leather) tanpa bahan kimia berbahaya, memuat hingga 8 kartu dan uang tunai.',
                'price' => 125000,
                'stock' => 22,
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'tas-aksesoris',
                'name' => 'Sarung Laptop Felt Daur Ulang 14 Inch',
                'description' => 'Sleeve pelindung laptop berbahan serat daur ulang tebal dengan penutup magnetik yang elegan, tahan gores dan tahan percikan air.',
                'price' => 110000,
                'stock' => 16,
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'tas-aksesoris',
                'name' => 'Pouch Kosmetik Kanvas Linen Polos',
                'description' => 'Tas serbaguna kecil berbahan linen katun tebal dengan ritsleting logam kuningan untuk menyimpan perlengkapan mandi atau kosmetik.',
                'price' => 89000,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'tas-aksesoris',
                'name' => 'Tas Belanja Lipat Parasut Daur Ulang',
                'description' => 'Tas belanja ultra-ringan yang bisa dilipat seukuran saku dompet, terbuat dari 100% serat botol plastik daur ulang yang kuat menahan beban hingga 15 kg.',
                'price' => 65000,
                'stock' => 45,
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],

            // --- 3. DEKORASI & RUMAH ---
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Lilin Aromaterapi Kedelai Alami (Soy Wax)',
                'description' => 'Lilin aromaterapi berbahan dasar 100% kedelai nabati dengan minyak atsiri lavender dan chamomile. Memberikan efek relaksasi hingga 45 jam pembakaran.',
                'price' => 80000,
                'stock' => 20,
                'image' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Stand Laptop Kayu Mahoni Ergonomis',
                'description' => 'Dudukan laptop dari kayu mahoni solid berpola alami yang dirancang ergonomis untuk mengurangi ketegangan leher dan meningkatkan sirkulasi udara laptop.',
                'price' => 185000,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Lampu Meja Bambu Minimalis Warm LED',
                'description' => 'Lampu meja berbahan bambu laminasi dengan pencahayaan LED kuning hangat yang hemat daya dan sakelar sentuh modern.',
                'price' => 245000,
                'stock' => 10,
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Tempat Tisu Kayu Jati Minimalis',
                'description' => 'Kotak tisu estetik berbahan kayu jati perhutani dengan finishing natural water-based yang aman dan menambah keindahan ruang tamu.',
                'price' => 115000,
                'stock' => 14,
                'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Diffuser Aromaterapi Keramik Ultrasonic',
                'description' => 'Alat pelembap udara dan penyebar aroma dengan penutup keramik elegan berfitur lampu malam hangat dan mati otomatis saat air habis.',
                'price' => 215000,
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Pot Tanaman Keramik Terracotta Buatan Tangan',
                'description' => 'Pot tanaman indoor berpori dari tanah liat terracotta dengan piringan penampung air untuk menjaga kelembapan optimal tanaman sukulen.',
                'price' => 105000,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Jam Meja Kayu Minimalis Aesthetic',
                'description' => 'Jam meja bundar berbahan kayu solid tanpa suara detak jarum (silent sweep), cocok untuk meja kerja minimalis atau nakas kamar tidur.',
                'price' => 175000,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'dekorasi-rumah',
                'name' => 'Keranjang Anyaman Seagrass Multifungsi',
                'description' => 'Keranjang serbaguna buatan tangan dari anyaman rumput laut alami untuk wadah tanaman hias, cucian pakaian, atau tempat mainan anak.',
                'price' => 195000,
                'stock' => 11,
                'image' => 'https://images.unsplash.com/photo-1595991209266-5ff5a3a2f008?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],

            // --- 4. ALAT MAKAN & DAPUR ---
            [
                'category_slug' => 'alat-makan-dapur',
                'name' => 'Set Alat Makan Bambu Portabel & Pouch',
                'description' => 'Set sendok, garpu, pisau, dan sumpit bambu alami lengkap dengan pouch kanvas praktis untuk mendukung gaya hidup bebas plastik sekali pakai.',
                'price' => 82000,
                'stock' => 35,
                'image' => 'https://images.unsplash.com/photo-1584362917165-526a968579e8?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'alat-makan-dapur',
                'name' => 'Wadah Bekal Stainless Steel 2 Tingkat',
                'description' => 'Lunch box susun 2 dari baja tahan karat anti bocor dengan sistem pengunci rapat, cocok untuk membawa bekal sehat ke kantor atau sekolah.',
                'price' => 165000,
                'stock' => 20,
                'image' => 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'alat-makan-dapur',
                'name' => 'Nampan Saji Kayu Pinus Estetik',
                'description' => 'Nampan serbaguna dari kayu pinus berserat cantik untuk menyajikan kopi, teh, dan camilan santai dengan nuansa cafe modern di rumah.',
                'price' => 140000,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'alat-makan-dapur',
                'name' => 'Tatakan Gelas Kayu Jati Set (6 pcs)',
                'description' => 'Set 6 buah coaster kayu jati padat tahan air dengan wadah penyimpanan kayu senada untuk melindungi meja dari bekas cangkir panas.',
                'price' => 95000,
                'stock' => 24,
                'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],

            // --- 5. PAKAIAN & TEKSTIL ORGANIK ---
            [
                'category_slug' => 'pakaian-tekstil-organik',
                'name' => 'Selimut Tenun Katun Organik Nusantara',
                'description' => 'Selimut lembut tenun tangan dengan motif etnik kontemporer berbahan serat katun daur ulang yang hangat dan nyaman digunakan sepanjang hari.',
                'price' => 220000,
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'pakaian-tekstil-organik',
                'name' => 'Apron Celemek Masak Linen Katun Organik',
                'description' => 'Celemek masak model silang belakang tanpa tali leher yang nyaman digunakan seharian, dilengkapi 2 saku depan berukuran luas.',
                'price' => 155000,
                'stock' => 16,
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'pakaian-tekstil-organik',
                'name' => 'Kaos Polos Katun Bambu Premium Unisex',
                'description' => 'T-shirt ramah lingkungan berbahan 70% serat bambu alami dan 30% katun organik. Terasa sangat sejuk di kulit, antibakteri alami, dan menyerap keringat dengan optimal.',
                'price' => 119000,
                'stock' => 35,
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
            [
                'category_slug' => 'pakaian-tekstil-organik',
                'name' => 'Syal Tenun Serat Alami Ecoprint',
                'description' => 'Syal eksklusif pewarna alam dengan teknik ecoprint dedaunan hutan tropis di atas kain sutra katun organik yang lembut dan mewah.',
                'price' => 185000,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ],
        ];

        foreach ($products as $item) {
            $cat = $categories->get($item['category_slug']);
            Product::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $cat?->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'image' => $item['image'],
                    'is_active' => $item['is_active'],
                ]
            );
        }
    }
}
