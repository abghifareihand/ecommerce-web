# EcoStore - Platform E-Commerce UMKM

Aplikasi web e-commerce modern untuk produk kriya dan gaya hidup ramah lingkungan, dibangun menggunakan **Laravel 12**, **Vue 3 (Inertia.js)**, dan **Tailwind CSS**.

---

## ✨ Fitur Utama

### 🛍️ Sisi Pengunjung (Storefront)
- **Landing Page Interaktif**: Beranda estetik dengan navigasi cepat, nilai keunggulan produk, dan cerita UMKM.
- **Katalog Belanja & Filter**: Pencarian produk, filter kategori, dan sortir harga.
- **Banner Promosi Dinamis**: Banner pengumuman dan promo produk unggulan.
- **Keranjang Belanja Real-Time**: Manajemen kuantitas produk dengan perhitungan otomatis.
- **Checkout Terintegrasi WhatsApp**: Format pesan otomatis ke WhatsApp toko untuk konfirmasi pemesanan.
- **Unduh Invoice Pesanan (PDF)**: Cetak bukti pesanan invoice resmi dengan logo dan rincian lengkap toko.

### 🛡️ Sisi Admin (Back-Office)
- **Dashboard Statistik**: Ringkasan performa penjualan, pesanan masuk, dan total produk.
- **Manajemen Produk**: Tambah, edit, unggah foto, kelola stok, dan hapus produk.
- **Kelola Pesanan**: Pemantauan status pesanan (`pending`, `confirmed`, `completed`, `cancelled`) dan opsi hapus pesanan yang dibatalkan.
- **Kelola Banner Promo**: Pengaturan banner aktif dan urutan tampilan.
- **Pengaturan Profil Admin**: Ubah nama, email, avatar foto profil, dan ganti kata sandi.
- **Pengaturan Identitas Toko Dinamis**: Pengaturan nama toko, slogan, nomor WhatsApp, alamat, dan upload logo toko dengan live preview.

---

## 🚀 Panduan Instalasi Lokal

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/abghifareihand/ecommerce-web.git
   cd ecommerce-web
   ```

2. **Instal Dependensi**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan pengaturan koneksi basis data di `.env` (default MySQL).*

4. **Migrasi & Seeder Database**:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Jalankan Aplikasi**:
   ```bash
   # Terminal 1: Backend
   php artisan serve

   # Terminal 2: Frontend
   npm run dev
   ```

---

## 🔑 Akun Bawaan (Development)
- **URL Admin**: `http://localhost:8000/admin/login`
- **Email**: `admin@example.com`
- **Password**: `password`
