# 🚀 Sistem Pendaftaran Magang (Intern Registration System)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-EBB308?style=for-the-badge&logo=filament)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss)](https://tailwindcss.com)

**Sistem Pendaftaran Magang** adalah platform berbasis web modern yang dirancang untuk mengelola seluruh alur pendaftaran peserta magang, mulai dari registrasi pelamar hingga manajemen oleh administrator. Dibangun dengan teknologi terbaru **Laravel 12** dan **Filament v3**, sistem ini menjamin performa yang cepat dan antarmuka yang elegan.

---

## ✨ Fitur Utama

- **Pendaftaran Online**: Formulir pendaftaran yang lengkap dan mudah digunakan bagi calon peserta.
- **Sistem OTP (One Time Password)**: Keamanan pendaftaran dengan fitur verifikasi kode OTP.
- **Manajemen Progress**: Pelamar dapat masuk kembali untuk melihat status aplikasi mereka (Pending, Diterima, atau Ditolak).
- **Dashboard Admin (Filament)**: Panel administrasi yang powerful untuk mengelola data pendaftaran, statistik, dan pengaturan sistem.
- **Cetak Bukti Otomatis**: Fitur unduh dan cetak lampiran pendaftaran dalam bentuk PDF.
- **Manajemen Universitas**: Sinkronisasi data universitas untuk mempermudah pengisian formulir.
- **Optimasi Lokal**: Script khusus untuk memastikan aplikasi berjalan ringan di lingkungan development.

---

## 🛠️ Persyaratan Sistem

Sebelum memulai, pastikan perangkat Anda memenuhi persyaratan berikut:
- **PHP** >= 8.2 (Direkomendasikan 8.3)
- **Composer** (Dependency Manager untuk PHP)
- **Node.js & NPM** (Untuk kompilasi assets Tailwind)
- **MySQL / MariaDB** (Sebagai database utama)
- **Web Server** (Bisa menggunakan Laragon, XAMPP, atau Artisan Serve)

---

## 🚀 Langkah Instalasi (Lokal)

Ikuti langkah-langkah di bawah ini untuk menyiapkan project di komputer Anda:

### 1. Clone Repository
```bash
git clone https://github.com/username/pendaftaranmagang.git
cd pendaftaranmagang
```

### 2. Instalasi Dependency
```bash
# Instal library backend
composer install

# Instal library frontend
npm install
```

### 3. Konfigurasi Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan pengaturan database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

Ubah bagian database di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pendaftaran_magang
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database
Jalankan migrasi untuk membuat tabel dan masukkan data awal:
```bash
php artisan migrate --seed
```

### 5. Konfigurasi File Storage
Agar file dokumen pelamar dapat diakses, jalankan:
```bash
php artisan storage:link
```

### 6. Menjalankan Aplikasi
Buka dua terminal dan jalankan perintah berikut:
```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Frontend Watcher (Vite)
npm run dev
```

Aplikasi dapat diakses di: `http://localhost:8000`
Akses Admin di: `http://localhost:8000/admin`

---

## ⚡ Optimasi Performa Lokal
Jika Anda merasa aplikasi berjalan agak lambat di lokal, gunakan script optimasi yang telah disediakan:
```bash
php optimize_local.php
```
Script ini akan membersihkan cache yang menumpuk dan memberikan saran konfigurasi `.env` terbaik.

---

## 🔑 Akun Akses Default
Secara default, Anda dapat menggunakan akun yang dibuat oleh seeder:
- **Email**: `test@example.com`
- **Password**: `password`

Untuk membuat admin baru, jalankan:
```bash
php artisan tinker
# Lalu tempel kode ini:
\App\Models\User::create(['name' => 'Admin Baru', 'email' => 'admin@pendaftaran.com', 'password' => bcrypt('password'), 'role' => 'admin']);
```

---

## ⚙️ Konfigurasi Sistem
Admin dapat mengakses menu **Settings** di Panel Admin untuk mengatur:
- Batasan jumlah pendaftar per universitas.
- Tanggal pembukaan dan penutupan pendaftaran.
- Template pesan OTP.
- Aktifkan/Nonaktifkan sistem pendaftaran secara global.

---

## 📂 Struktur Project (Penting)
- `app/Filament/`: Konfigurasi resource panel admin.
- `app/Http/Controllers/`: Logika registrasi dan pendaftaran (`PendaftaranController`).
- `resources/views/`: Layout dan template frontend.
- `database/seeders/`: Data awal untuk universitas dan user.
- `routes/web.php`: Daftar rute aplikasi.

---

## 💻 Panduan Git & GitHub (Workflow)

Untuk kolaborasi di GitHub, harap perhatikan aturan berikut:

1. **Sinkronisasi**: Selalu jalankan `git pull origin main` sebelum mulai coding.
2. **Branching**: Jangan bekerja langsung di branch `main`. Buat branch baru:
   ```bash
   git checkout -b feature/nama-fitur-baru
   ```
3. **Commit**: Gunakan prefix standar dalam pesan commit:
   - `feat:` Untuk fitur baru.
   - `fix:` Untuk perbaikan bug.
   - `docs:` Untuk perubahan dokumentasi.
4. **Push & Pull Request**:
   ```bash
   git push origin feature/nama-fitur-baru
   ```
   Setelah itu, buat Pull Request di halaman GitHub repository.

---

## 📄 Lisensi
Sistem ini menggunakan [MIT License](LICENSE).

---

> Dibuat untuk efisiensi pendaftaran magang yang lebih baik. 🌟
