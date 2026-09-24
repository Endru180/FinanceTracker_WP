<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# FinanceTracker_WP

Finance Tracker dengan AI Finance Advisor — project mata kuliah Web Programming (COMP6821001).

Aplikasi pencatat keuangan yang dilengkapi AI advisor. AI memberi observasi & saran lunak soal pola pengeluaran, serta bisa ditanya soal dampak suatu rencana pembelian terhadap keuangan user.

## Tech Stack

- **Framework:** Laravel 11 (PHP)
- **Database:** MySQL
- **Frontend:** Blade + Tailwind CSS (bawaan skeleton Laravel), dikompilasi via Vite
- **AI:** LLM pihak ketiga (dipanggil via API)

## Prasyarat

Sebelum clone/setup, pastikan sudah terinstall di komputer masing-masing:

- **PHP 8.2+** dan **Composer** — disarankan pakai [Laragon](https://laragon.org/) (bundle PHP + MySQL + Composer sekaligus, tinggal install satu aplikasi)
- **Node.js** dan **npm**
- **Git**

## Setup Project (untuk anggota tim baru)

Ikuti urutan ini persis — jangan diloncat, karena tiap langkah bergantung ke langkah sebelumnya.

### 1. Siapkan database lokal

Ini **tidak bisa di-skip** — database MySQL harus ada di komputer masing-masing sebelum lanjut, karena tidak ada database yang di-share lewat repo ini.

- Nyalain MySQL (lewat Laragon: klik "Start All" atau pastikan status MySQL aktif)
- Buka HeidiSQL (Menu Laragon → Database) atau phpMyAdmin
- Bikin database baru dengan nama persis: `finance_tracker`

### 2. Clone repository

```
git clone https://github.com/Endru180/FinanceTracker_WP.git
cd FinanceTracker_WP
```

### 3. Install dependency PHP

```
composer install
```

Kalau muncul error soal security advisory saat pertama kali install, jalankan dulu:
```
composer config policy.advisories.block false
```
lalu ulangi `composer install`.

### 4. Setup file environment

```
copy .env.example .env
php artisan key:generate
```

Cek isi `.env` — bagian database seharusnya sudah otomatis terisi:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=root
DB_PASSWORD=
```
Kalau setup MySQL lokal kamu beda (misal pakai password), sesuaikan baris ini saja.

### 5. Jalankan migration

```
php artisan migrate
```

Kalau berhasil tanpa error, berarti koneksi ke database sudah benar.

### 6. Install & compile dependency frontend

```
npm install
npm run build
```

### 7. Jalankan server

```
php artisan serve
```

Buka `http://127.0.0.1:8000` — kalau muncul halaman welcome Laravel dengan tampilan yang sudah ter-styling, setup selesai.

## Troubleshooting Umum

- **`php`/`composer` tidak dikenali di terminal** — belum ditambahkan ke PATH sistem Windows. Cek lewat Laragon: Menu → Tools → Quick add → Path.
- **Composer error "Permission denied" saat download package** — biasanya disebabkan Windows Defender real-time scan. Tambahkan folder project sebagai exclusion di Windows Security → Virus & threat protection → Manage settings → Exclusions.
- **`php artisan migrate` error connection refused** — pastikan service MySQL di Laragon sedang aktif (Start All).
