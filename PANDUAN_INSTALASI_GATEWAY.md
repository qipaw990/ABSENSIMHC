# 📖 PANDUAN INSTALASI APLIKASI ABSENSI QR CODE & INTEGRASI WHATSAPP GATEWAY
> **Developer:** qpawdeveloper  
> **Official Documentation:** Technical & Deployment Guide

Dokumen ini berisi panduan langkah-demi-langkah untuk melakukan **Instalasi Aplikasi Sistem Absensi QR Code** serta **Pengaturan & Integrasi WhatsApp Gateway (WA Gateway)** buatan **qpawdeveloper**.

---

## 📑 DAFTAR ISI
1. [Arsitektur Sistem & Prasyarat](#1-arsitektur-sistem--prasyarat)
2. [Instalasi Aplikasi Absensi QR Code](#2-instalasi-aplikasi-absensi-qr-code)
   - [Metode A: Instalasi Otomatis 1-Klik (Docker / Linux / CasaOS)](#metode-a-instalasi-otomatis-1-klik-docker--linux--casaos)
   - [Metode B: Instalasi Manual dengan Docker Compose](#metode-b-instalasi-manual-dengan-docker-compose)
   - [Metode C: Instalasi Local Development (XAMPP / Windows tanpa Docker)](#metode-c-instalasi-local-development-xampp--windows-tanpa-docker)
3. [Panduan Integrasi WhatsApp Gateway (WA Gateway)](#3-panduan-integrasi-whatsapp-gateway-wa-gateway)
   - [Konsep & Cara Kerja WA Gateway](#konsep--cara-kerja-wa-gateway)
   - [Langkah 1: Konfigurasi Environment Aplikasi (.env)](#langkah-1-konfigurasi-environment-aplikasi-env)
   - [Langkah 2: Penyiapan Server / Service WA Gateway](#langkah-2-penyiapan-server--service-wa-gateway)
   - [Langkah 3: Konfigurasi WA Sender di Dashboard Super Admin](#langkah-3-konfigurasi-wa-sender-di-dashboard-super-admin)
   - [Langkah 4: Hubungkan WA Sender ke Data Kelas](#langkah-4-hubungkan-wa-sender-ke-data-kelas)
4. [Pengujian & Verifikasi System](#4-pengujian--verifikasi-system)
5. [Pemeliharaan, Backup & Update](#5-pemeliharaan-backup--update)

---

## 1. 🏗️ ARSITEKTUR SISTEM & PRASYARAT

### A. Arsitektur Sistem
* **Aplikasi Utama**: Laravel 11 (PHP 8.2+), MySQL 8.0, Nginx / Apache, Web-based QR Scanner.
* **WhatsApp Gateway Service**: REST API Gateway terpisah yang berfungsi menghubungkan aplikasi absensi dengan perangkat WhatsApp (HP Android / Node.js Engine) untuk mengirim notifikasi real-time ke orang tua siswa.

```
+------------------------+      HTTP REST API       +-------------------------+      WhatsApp Web      +-----------------------+
|  Aplikasi Absensi MHC  |  -------------------->  |   WhatsApp Gateway      |  --------------------> |  HP Orang Tua Siswa   |
| (Laravel 11 / Docker)  |   (X-API-KEY Auth)      | (Node.js/Baileys/Server)|    (Nomor WA Sender)   | (Pesan Notifikasi WA) |
+------------------------+                          +-------------------------+                        +-----------------------+
```

### B. Prasyarat Server / Spesifikasi Minimum
* **Operating System**: Linux (Ubuntu 20.04+, Debian, CasaOS) atau Windows 10/11 (dengan Docker Desktop / XAMPP).
* **RAM**: Minimal 2 GB (Direkomendasikan 4 GB+).
* **Disk**: Minimal 10 GB SSD.
* **Software**:
  * **Docker & Docker Compose** (Sangat direkomendasikan untuk instalasi cepat & konsisten).
  * Atau **PHP 8.2+**, **Composer 2.x**, **MySQL 8.0**, **Nginx/Apache** jika instalasi manual non-Docker.
* **Koneksi Internet / LAN**: Diperlukan agar HP Admin/Guru dan HP WA Sender dapat terhubung ke server.

---

## 2. 🚀 INSTALASI APLIKASI ABSENSI QR CODE

### Metode A: Instalasi Otomatis 1-Klik (Docker / Linux / CasaOS) — *Rekomendasi*

1. Upload / Clone seluruh folder project ke direktori server (contoh: `/DATA/AppData/absensi_qrcode` atau `/home/ubuntu/absensi_qrcode`).
2. Masuk ke terminal server dan berikan hak akses eksekusi script:
   ```bash
   cd /DATA/AppData/absensi_qrcode
   chmod +x install.sh update.sh backup.sh deploy-server.sh
   ```
3. Jalankan script installer interaktif:
   ```bash
   ./install.sh
   ```
4. Masukkan konfigurasi saat diminta (atau tekan **Enter** untuk menggunakan default):
   * **Nama Sekolah**: (misal: `SMK MUTHIA HARAPAN CICALENGKA`)
   * **URL / Domain**: (misal: `https://absensi.sekolah.sch.id` atau `http://192.168.1.100:8585`)
   * **Port Aplikasi**: `8585`
   * **Port phpMyAdmin**: `8586`
   * **Email Super Admin**: `admin@sekolah.com`
   * **Password Super Admin**: `AdminAbsensi2025!`
5. Script akan otomatis membangun container Docker, menjalankan database migration, seeding data awal, dan mengonfigurasi akun Super Admin.
6. Selesai! Buka browser di `http://IP_SERVER:8585` untuk login.

---

### Metode B: Instalasi Manual dengan Docker Compose

Jika Anda ingin mengonfigurasi `docker-compose.yml` secara manual:

1. **Salin file `.env.example` ke `.env`**:
   ```bash
   cp .env.example .env
   ```
2. **Edit file `.env`** sesuaikan dengan domain dan database Anda:
   ```env
   APP_NAME="Sistem Absensi MHC"
   APP_ENV=production
   APP_KEY=base64:nLFw8vkkqzZtbYb+jb9NW7r3xM4PgW1TAEcgc2MFoEg=
   APP_DEBUG=false
   APP_URL=http://localhost:8585
   NAMA_SEKOLAH="SMK MUTHIA HARAPAN CICALENGKA"

   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=absensi_mhc
   DB_USERNAME=absensi_user
   DB_PASSWORD=AbsensiMHC@2025

   WA_GATEWAY_BASE_URL=https://api-gateway.smkmuthiaharapanclk.com
   WA_GATEWAY_API_KEY=wag_admin_key_changeme_12345678
   ```
3. **Jalankan Container Docker**:
   ```bash
   docker compose up -d --build
   ```
4. **Jalankan Artisan Migration & Seeder**:
   ```bash
   docker exec absensi_app php artisan key:generate --force
   docker exec absensi_app php artisan migrate:fresh --seed --force
   docker exec absensi_app php artisan storage:link --force
   ```
5. **Buat Akun Super Admin**:
   ```bash
   docker exec absensi_app php artisan tinker --execute="
   \$user = \App\Models\User::firstOrCreate(['email' => 'admin@sekolah.com'], [
       'name' => 'Super Admin',
       'password' => \Illuminate\Support\Facades\Hash::make('AdminAbsensi2025!'),
   ]);
   \$user->assignRole('super_admin');
   "
   ```

---

### Metode C: Instalasi Local Development (XAMPP / Windows tanpa Docker)

1. Pastikan XAMPP menggunakan **PHP 8.2+** dan MySQL berjalan.
2. Buat database baru di phpMyAdmin bernama `absensi_mhc`.
3. Buka Command Prompt / PowerShell di folder project:
   ```cmd
   cp .env.example .env
   composer install
   php artisan key:generate
   ```
4. Ubah `.env` untuk koneksi MySQL lokal:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=absensi_mhc
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Jalankan Migration & Seeding:
   ```cmd
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```
6. Jalankan Server Lokal:
   ```cmd
   php artisan serve --port=8585
   ```
   Aplikasi dapat diakses melalui `http://localhost:8585`.

---

## 3. 📱 PANDUAN INTEGRASI WHATSAPP GATEWAY (WA GATEWAY)

### Konsep & Cara Kerja WA Gateway
Aplikasi Absensi terhubung ke **WhatsApp Gateway API** melalui `WaGatewayService`. Setiap kali siswa melakukan scan QR code absensi (atau guru mengubah status kehadiran/izin), aplikasi akan membuat request HTTP POST ke endpoint Gateway.

Spesifikasi Endpoint WA Gateway yang digunakan aplikasi:
* **Pengiriman Pesan**: `POST {WA_GATEWAY_BASE_URL}/api/messages/send`
  * **Header**: `X-API-KEY: {API_KEY_GATEWAY}`
  * **Body (JSON)**:
    ```json
    {
      "phone": "6281234567890",
      "message": "Pesan Notifikasi Absensi...",
      "media_url": "https://... (optional)",
      "media_type": "image (optional)"
    }
    ```
* **Pemeriksaan Status Gateway**: `GET {WA_GATEWAY_BASE_URL}/health`
  * **Header**: `X-API-KEY: {API_KEY_GATEWAY}`
  * **Response**: `{"success": true, "status": "running"}`

---

### Langkah 1: Konfigurasi Environment Aplikasi (.env)

Buka file `.env` aplikasi absensi (atau atur via environment Docker) dan tentukan URL server WA Gateway & API Key Default Admin:

```env
# URL WA Gateway (dapat berupa URL Cloud HTTPS atau IP Local Server WA Gateway)
WA_GATEWAY_BASE_URL=https://api-gateway.smkmuthiaharapanclk.com

# Default API Key Admin WhatsApp Gateway
WA_GATEWAY_API_KEY=wag_admin_key_changeme_12345678
```

> **Catatan:** Jika menggunakan Docker Compose, pastikan variabel `WA_GATEWAY_BASE_URL` dan `WA_GATEWAY_API_KEY` juga sudah sesuai di file `docker-compose.yml` pada bagian environment service `app`.

---

### Langkah 2: Penyiapan Server / Service WA Gateway

Anda dapat menghubungkan aplikasi ke salah satu pilihan server WhatsApp Gateway berikut:

1. **Option 1: Custom WA Gateway API Server (Node.js / Baileys / WhatsApp HTTP API)**
   * Pastikan server WA Gateway berjalan dan dapat diakses oleh server Absensi.
   * Dapatkan **API Key** pengirim dari Dashboard WA Gateway.
2. **Option 2: Android WA Gateway App**
   * Jalankan aplikasi Android WA Gateway pada smartphone khusus sekolah.
   * Hubungkan aplikasi Android ke server WA Gateway.
3. **Option 3: Layanan Cloud WA Gateway (seperti Fonnte/Wablas)**
   * Masukkan Base URL layanan cloud dan gunakan Token/API Key yang disediakan provider.

---

### Langkah 3: Konfigurasi WA Sender di Dashboard Super Admin

Aplikasi Absensi mendukung **Multi-Sender per Kelas**, sehingga notifikasi dapat dikirimkan dari nomor WhatsApp yang berbeda sesuai wali kelas/jurusan.

Langkah-langkah di Dashboard Aplikasi:

1. Login sebagai **Super Admin**.
2. Masuk ke menu **WA Sender** (Sidebar ➔ WA Sender).
3. Klik tombol **+ Tambah Sender Baru**.
4. Isi formulir pendaftaran Sender:
   * **Nama Sender**: Contoh `WA Sender Kelas X RPL 1` atau `WA Official Sekolah`.
   * **Nomor WhatsApp**: Nomor HP pengirim (format: `081234567890` atau `6281234567890`).
   * **API Key WhatsApp Gateway**: Key khusus device dari Gateway (atau kosongkan untuk memakai default key dari `.env`).
   * **Status**: Aktif.
5. Klik **Simpan**.
6. Klik tombol **Tes Koneksi / Cek Status** pada tabel WA Sender untuk memastikan status berubah menjadi `<span style="color:green">● Aktif</span>`.
7. Klik ikon **Kirim Pesan Uji Coba** untuk mengetes pengiriman pesan langsung ke HP Anda.

---

### Langkah 4: Hubungkan WA Sender ke Data Kelas

Agar notifikasi absensi siswa terkirim melalui nomor WA Sender yang tepat:

1. Masuk ke menu **Data Kelas** (Sidebar ➔ Master Data ➔ Kelas).
2. Edit / Tambah **Kelas** (contoh: `X RPL 1`).
3. Pilih **WA Sender** yang bertanggung jawab untuk kelas tersebut pada dropdown **Sender WhatsApp**.
4. Klik **Simpan**.

Sekarang, setiap siswa di kelas `X RPL 1` yang melakukan scan QR akan memicu notifikasi WA otomatis dari WA Sender kelas tersebut!

---

## 4. 🧪 PENGUJIAN & VERIFIKASI SYSTEM

### A. Verifikasi Status WA Gateway via Command Line
Anda dapat memeriksa status koneksi WA Sender langsung dari terminal server:
```bash
# Untuk instalasi Docker:
docker exec absensi_app php artisan wa:check-sender

# Untuk instalasi non-Docker:
php artisan wa:check-sender
```

### B. Uji Coba Scan QR & Log Notifikasi WA
1. Tambahkan 1 Data Siswa uji coba dengan memasukkan **Nomor WA Orang Tua** yang aktif (HP Anda).
2. Login sebagai **Guru / Wali Kelas** ➔ Masuk menu **Scan Absensi**.
3. Pilih Kelas siswa uji coba.
4. Scan QR Code siswa (atau gunakan fitur Input Manual Presensi).
5. **Cek HP**: Pesan notifikasi absensi (status Hadir / Terlambat) akan masuk ke WhatsApp Orang Tua secara real-time.
6. **Cek Log System**: Super Admin dapat melihat riwayat dan status pengiriman di menu **Log WhatsApp** (`WaLog`).

---

## 5. 🛠️ PEMELIHARAAN, BACKUP & UPDATE

### A. Update Aplikasi Tanpa Kehilangan Data
Untuk memperbarui aplikasi ke versi terbaru tanpa menghapus data database/storage:
```bash
chmod +x update.sh
./update.sh
```

### B. Backup Database & File Storage
Lakukan backup rutin database dan file absensi:
```bash
chmod +x backup.sh
./backup.sh
```
File backup berformat `.tar.gz` akan otomatis dibuat di folder `backups/`.

### C. Menangani WA Sender Disconnected
Jika status WA Sender berubah menjadi `Terputus`:
1. Pastikan HP / Server WhatsApp Gateway terhubung ke internet.
2. Buka Dashboard WhatsApp Gateway ➔ Scan ulang QR Code WhatsApp Web jika session terlepas.
3. Masuk ke menu **WA Sender** di Aplikasi Absensi ➔ Klik **Cek Status**.

---

*Developed by **qpawdeveloper** — Technical Documentation Guide.*
