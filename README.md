# Novastra PHP

Novastra adalah toko bahan masak segar berbasis Laravel 13 dan PHP 8.5. Aplikasi ini tidak membutuhkan Node.js di server production: Vite/Tailwind dibangun di komputer lokal, lalu hasil build bersama aplikasi PHP diunggah ke cPanel.

## Fitur yang sudah dimigrasikan

- Katalog produk dan kategori, pencarian, filter, produk unggulan, dan stok.
- Cart berbasis browser dan checkout yang mewajibkan login pelanggan.
- Registrasi dan login username/password terpisah untuk setiap pelanggan; Google OAuth dinonaktifkan.
- Pesanan pelanggan, status pembayaran, histori status, feedback, dan kontak WhatsApp.
- Admin dashboard berisi omzet, jumlah pesanan, nilai rata-rata pesanan, stok menipis, produk terlaris, dan kunjungan halaman.
- Kelola produk, kategori, pesanan, pembayaran, feedback, serta ekspor laporan Excel.
- Upload gambar tervalidasi dengan nama acak dan direktori yang dikendalikan server.
- Layout responsif untuk handphone, tablet, dan laptop.

## Kebutuhan server

- PHP 8.5 dengan ekstensi `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `session`, `tokenizer`, `xml`, dan `zip`.
- MariaDB atau MySQL.
- Apache/LiteSpeed dengan `mod_rewrite` dan document root yang dapat diarahkan ke folder `public`.
- Composer 2 hanya diperlukan di server bila folder `vendor` tidak dibangun dan diunggah dari komputer lokal.
- Node.js hanya diperlukan di komputer lokal untuk membangun aset frontend.

## Menjalankan secara lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Atur database pada `.env`, lalu jalankan:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Akun seed untuk development:

- Pelanggan demo lokal: `pelanggan` / `novastra123`
- Admin: `admin` / password lokal bawaan `adminnovastra123`

Jangan gunakan kredensial development di production.

## Deployment ke cPanel

### Deployment berulang melalui Git dan script

Untuk repository yang di-clone melalui **Git Version Control**, gunakan struktur berikut:

```text
/home/CPANEL_USER/
├── repositories/novastra-php/  # repository Laravel
├── secret/.env                 # environment production
├── logs/                       # log deployment
└── public_html/                # document root website
```

Siapkan environment production di luar repository:

```bash
mkdir -p /home/CPANEL_USER/secret /home/CPANEL_USER/logs
cp .env /home/CPANEL_USER/secret/.env
chmod 700 /home/CPANEL_USER/secret /home/CPANEL_USER/logs
chmod 600 /home/CPANEL_USER/secret/.env
```

Pastikan `secret/.env` memiliki `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_KEY` production yang tetap. Jangan menjalankan `key:generate` saat redeploy.

Sebelum commit dan push dari komputer lokal, bangun serta verifikasi aset frontend:

```bash
./pre-deploy.sh
git add -A
git commit -m "build: prepare production release"
git push origin novastra-php
```

Folder `public/build` adalah artefak production yang dilacak Git. Jangan commit `node_modules`, `vendor`, atau `.env`.

Setelah melakukan **Update from Remote** di cPanel, jalankan satu perintah berikut melalui Terminal atau Cron Jobs:

```bash
COMPOSER_BIN=/home/CPANEL_USER/bin/composer.phar /bin/bash /home/CPANEL_USER/repositories/novastra-php/deploy.sh
```

Script akan menjalankan preflight, memasang `.env`, maintenance mode, Composer production install, memverifikasi frontend prebuilt, mempublikasikan folder `public` ke `public_html`, menjalankan migration, optimasi, verifikasi, lalu mengaktifkan aplikasi kembali. Node.js dan npm tidak diperlukan di server. Folder `public_html/uploads` dan `.htaccess` milik cPanel tidak ditimpa saat publikasi. Output disimpan di `/home/CPANEL_USER/logs/deploy-YYYYMMDD-HHMMSS.log`, sedangkan hasil deployment terakhir tersedia di `/home/CPANEL_USER/logs/latest-status.txt`. Seeder tidak dijalankan agar data production tidak tertimpa. Jika salah satu tahap gagal setelah maintenance mode aktif, aplikasi tetap dalam maintenance mode sampai deployment berikutnya berhasil agar kode parsial tidak disajikan kepada pengunjung.

Logging menggunakan redirect file langsung agar stabil saat dijalankan melalui Cron Jobs. `latest-status.txt` akan berisi `RUNNING` ketika proses dimulai dan berubah menjadi `SUCCESS` atau `FAILED` setelah proses selesai; output Cron boleh dikosongkan karena detailnya sudah tersimpan di folder `logs`.

Build frontend lokal membutuhkan Node.js 20.19+, 22.12+, atau versi yang lebih baru. Server hanya memerlukan PHP CLI, Composer, dan `rsync`. Jika binary PHP atau Composer berada di lokasi lain, berikan `PHP_BIN` dan `COMPOSER_BIN` saat menjalankan `deploy.sh`.

Cron Jobs menjalankan perintah sesuai jadwal dan bukan tombol manual. Jangan menjadwalkan script setiap menit jika deployment hanya dilakukan setelah pull; gunakan Terminal untuk eksekusi langsung atau buat jadwal satu kali lalu hapus kembali.

### 1. Siapkan database

Di cPanel, buat database dan user MySQL melalui **MySQL Databases**, berikan seluruh privilege, lalu catat nama database, username, password, dan host database.

### 2. Siapkan aplikasi di komputer lokal

Salin `.env.example` menjadi `.env.production`. Aplikasi tetap menerima nama variabel dari versi Next.js sebelumnya, sehingga konfigurasi production berikut dapat digunakan:

```dotenv
NODE_ENV=production
DATABASE_URL="mysql://USER_DATABASE:PASSWORD_DATABASE@localhost:3306/NAMA_DATABASE"
AUTH_SECRET=rahasia-acak-minimal-32-karakter
AUTH_URL=https://novastra.my.id
NEXT_PUBLIC_APP_URL=https://novastra.my.id

ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH='$2y$12$HASH_BCRYPT_ANDA'
ADMIN_EMAIL=Novastra.partners@gmail.com

SMTP_HOST=mail.novastra.my.id
SMTP_PORT=587
SMTP_USER=support@novastra.my.id
SMTP_PASSWORD=password-email
SMTP_FROM=support@novastra.my.id

WHATSAPP_ADMIN_NUMBER=6281234567890
PUBLIC_UPLOAD_DIR=/home/CPANEL_USER/public_html/uploads
PRIVATE_UPLOAD_DIR=/home/CPANEL_USER/repositories/novastra-php/storage/app/private
TEMP_UPLOAD_DIR=/home/CPANEL_USER/repositories/novastra-php/storage/app/tmp
```

Gunakan URL biasa tanpa karakter escape Markdown: tulis `@`, bukan `\@`, dan jangan membungkus URL dengan format tautan. Jika username atau password database berisi karakter khusus seperti `@`, `:`, `/`, atau `#`, URL-encode nilai tersebut. `AUTH_SECRET` digunakan sebagai sumber kunci enkripsi Laravel ketika `APP_KEY` tidak tersedia.

`ADMIN_PASSWORD_HASH` harus berupa hash bcrypt dan harus diapit tanda petik tunggal agar karakter `$` tidak diproses oleh parser `.env`. Hash dapat dibuat di komputer lokal:

```bash
php -r "echo password_hash('GANTI_DENGAN_PASSWORD_KUAT', PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
```

Jangan menyimpan password admin plaintext atau hash production di Git. `SMTP_FROM` harus berupa alamat email valid; nama pengirim mengikuti `APP_NAME`.

Bangun dependency production dan aset frontend:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:clear
```

Folder `public/build` dan `vendor` hasil perintah tersebut harus ikut diunggah. Folder `node_modules`, `.git`, `.next`, `tests`, dan file `.env` lokal tidak perlu diunggah.

### 3. Upload aplikasi

Cara yang paling aman:

1. Upload seluruh proyek ke folder di luar `public_html`, misalnya `/home/CPANEL_USER/novastra`.
2. Atur document root domain atau subdomain ke `/home/CPANEL_USER/novastra/public` melalui menu **Domains** di cPanel.
3. Upload `.env.production` sebagai `/home/CPANEL_USER/novastra/.env`.

Dengan susunan ini, source code, `.env`, `vendor`, dan `storage` tidak dapat diakses langsung dari web. Jangan arahkan document root ke root proyek.

Jika paket hosting tidak mengizinkan perubahan document root, hubungi Rumahweb agar document root diarahkan ke folder `public`. Memindahkan seluruh aplikasi ke `public_html` tanpa perlindungan dapat membuka file rahasia dan tidak disarankan.

### 4. Atur permission

Folder berikut harus dapat ditulis oleh proses PHP:

```text
storage
bootstrap/cache
public/uploads
```

Gunakan permission `755` atau `775` sesuai user/group hosting. Jangan gunakan `777`.

### 5. Migrasi database dan optimasi

Jika cPanel menyediakan Terminal, jalankan dari root aplikasi:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan optimize
```

Seeder production hanya membuat akun admin dan katalog contoh. Setiap pelanggan membuat akun pribadi melalui halaman registrasi. Pada deployment berikutnya, cukup jalankan `php artisan migrate --force`; jangan menjalankan seeder lagi kecuali memang ingin memperbarui data contoh.

Jika Terminal tidak tersedia, minta Rumahweb mengaktifkan SSH/Terminal atau jalankan perintah Artisan melalui cron satu kali. Jangan membuat route web publik untuk menjalankan migration.

### 6. Konfigurasi email cPanel

Isi variabel `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, dan `SMTP_FROM` seperti contoh production di atas. Sesuaikan port dan encryption dengan detail **Connect Devices** dari cPanel Email Accounts.

### 7. Pemeriksaan setelah deploy

- Buka halaman utama, produk, cart, dan login pelanggan.
- Buat satu pesanan dummy dan pastikan total serta stok berubah sesuai status.
- Login ke `/admin/login`, periksa dashboard, produk, pesanan, dan ekspor laporan.
- Pastikan upload gambar bekerja dan file masuk ke `public/uploads/products`.
- Pastikan `APP_DEBUG=false` dan URL seperti `/.env`, `/composer.json`, serta `/storage/logs/laravel.log` tidak dapat diakses.

## Backup wajib

Backup database, `public/uploads`, dan `storage/app/private` secara berkala. Simpan backup di lokasi berbeda dari hosting utama.
