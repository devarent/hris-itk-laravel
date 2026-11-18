# HRIS ITK — Sistem Informasi Manajemen Karyawan

Aplikasi web manajemen SDM berbasis **Laravel 11** dengan fitur import Excel, absensi, pengajuan izin, penilaian kinerja, penggajian, dan laporan. Antarmuka memakai Tailwind CSS dengan komponen bergaya Shadcn.

## Fitur

- Autentikasi & role (`admin`, `atasan`, `karyawan`)
- Master data: jabatan, satuan kerja, karyawan, komponen gaji
- Import Excel: karyawan & absensi, dengan template unduh
- Absensi harian, rekap, dan verifikasi
- Pengajuan izin/cuti dengan alur persetujuan
- Penugasan & penilaian kinerja
- Penggajian: hitung otomatis, konfirmasi, bayar, slip gaji PDF
- Laporan: ekspor Excel, PDF, Word, dan PPTX

## Teknologi

| Komponen | Versi |
| --- | --- |
| PHP | 8.2+ |
| Laravel | 11.x |
| Database | MySQL / MariaDB |
| Frontend | Blade + Tailwind CSS + Vite |
| Excel | PhpSpreadsheet |
| PDF | DOMPDF |

## Instalasi lokal

```bash
git clone https://github.com/aryafatthurahman4-collab/hris-itk-laravel.git
cd hris-itk-laravel

composer install
npm install

cp .env.example .env
php artisan key:generate

# Sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
php artisan migrate
php artisan db:seed

npm run build
php artisan serve
```

Aplikasi berjalan di `http://127.0.0.1:8000` (atau `http://hris-itk-ijk.test` bila memakai Laragon).

## Akun demo (lokal)

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@hr.com` | `password` |
| Atasan | `atasan@hr.com` | `password` |
| Karyawan | `karyawan@hr.com` | `password` |

Akun ini dibuat oleh `Database\Seeders\UserSeeder`. **Ganti password sebelum digunakan di lingkungan publik.**

## Perintah umum

```bash
php artisan serve              # Jalankan server
npm run dev                    # Watch aset frontend
php artisan migrate            # Migrasi
php artisan db:seed            # Isi data demo
php artisan migrate:fresh --seed   # Reset + isi ulang database
php artisan optimize:clear     # Bersihkan cache
php artisan test               # Jalankan tes
```

## Catatan keamanan

- File `.env`, `.env.production`, dan `bootstrap/cache/*.php` **tidak boleh** di-commit. Semuanya sudah ada di `.gitignore`.
- Route `/install-db` hanya terdaftar saat `APP_ENV=local` dan `APP_DEBUG=true`. Gunakan `php artisan migrate:fresh --seed` di produksi, bukan route tersebut.
- Jangan simpan passwordPlain di dalam skrip atau kode. Gunakan variabel lingkungan.
- Sebelum deploy: `php artisan key:generate`, set `APP_ENV=production`, `APP_DEBUG=false`, lalu `php artisan optimize`.

## Deployment

Lihat [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) dan [DATABASE_SETUP_GUIDE.md](DATABASE_SETUP_GUIDE.md).

## Struktur database

Tabel utama: `jabatan`, `satuan_kerja`, `users`, `karyawan`, `absensi`, `pengajuan_izin`, `tugas_karyawan`, `penilaian_kinerja`, `komponen_gaji`, `penggajian`, `detail_penggajian`, `import_logs`.

## Lisensi

MIT.

---

## Project Governance

[![CI](https://github.com/aryafatthurahman4-collab/hris-itk-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/aryafatthurahman4-collab/hris-itk-laravel/actions/workflows/ci.yml)

[Contributing](CONTRIBUTING.md) | [Security Policy](SECURITY.md) | [Code of Conduct](CODE_OF_CONDUCT.md)