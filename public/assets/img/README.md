# Direktori Logo & Aset Bawaan (Default Assets)

Folder ini (`public/assets/img/`) digunakan untuk menyimpan aset gambar bawaan website seperti logo resmi toko dan ikon brand.

### File Logo Bawaan:
- `logo.png` : Logo default utama toko (mendukung PNG transparan atau JPG).
- `logo.svg` : Logo vektor default alternatif jika tersedia.

### Panduan Penggunaan:
1. Anda dapat menaruh atau mengganti file `logo.png` atau `logo.svg` di dalam folder ini kapan saja.
2. File di folder ini otomatis dijadikan fallback default di:
   - Header & Footer Toko Pengunjung (`ShopLayout`)
   - Header Landing Page (`LandingLayout`)
   - Sidebar Dashboard Admin (`AdminLayout`)
   - Halaman Login Admin (`Login`)
   - Dokumen Cetak Invoice PDF (`order-pdf`)
3. Ketika Admin mengunggah logo custom melalui menu **Pengaturan Toko**, logo yang diunggah akan disimpan di `storage/app/public/store/`. Jika logo custom tersebut dihapus, tampilan logo akan otomatis kembali menggunakan `public/assets/img/logo.png`.
