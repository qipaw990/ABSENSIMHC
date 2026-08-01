# 🚀 PANDUAN KOMERSIALISASI & INSTALASI APLIKASI ABSENSI QR CODE & WA

Dokumen ini berisi **Panduan Penjualan, Strategi Bisnis, serta Panduan Instalasi 1-Klik** untuk menjual aplikasi **Sistem Absensi QR Code & Notifikasi WhatsApp Otomatis** ke sekolah-sekolah atau klien.

---

## 📑 DAFTAR ISI
1. [Fitur Unggulan (Brosur Penjualan)](#1-fitur-unggulan-brosur-penjualan)
2. [Paket Harga & Strategi Komersial](#2-paket-harga--strategi-komersial)
3. [Panduan Instalasi 1-Klik (Untuk Pembeli)](#3-panduan-instalasi-1-klik-untuk-pembeli)
4. [Alur Penggunaan Aplikasi (Quick Start)](#4-alur-penggunaan-aplikasi-quick-start)
5. [Pemeliharaan & Update System](#5-pemeliharaan--update-system)

---

## 1. 🌟 FITUR UNGGULAN (BROSUR PENJUALAN)

Gunakan poin-poin ini saat menawarkan ke pihak Sekolah (Kepala Sekolah/Wakasek/Kurikulum):

* **⚡ Scan QR Code Super Cepat**: Pakai kamera HP, Tablet, Laptop, maupun Scanner QR USB tanpa perangkat mahal.
* **📱 Notifikasi WhatsApp Otomatis ke Orang Tua**: Begitu siswa scan QR, pesan WA otomatis terkirim ke WhatsApp Orang Tua (Status: Hadir / Terlambat / Alpha / Izin).
* **🪪 Cetak Batch Kartu QR Pelajar (PDF)**: Sekali klik bisa cetak kartu pelajar berisi QR Code untuk 1 kelas penuh siap cetak/laminating.
* **📊 Rekapitulasi & Laporan Otomatis**: Rekap harian, bulanan, per siswa, dan export PDF/Excel secara instant.
* **⏰ Pengaturan Jam Absensi Fleksibel**: Atur batas jam masuk, batas jam keterlambatan, dan hari libur per kelas.
* **👥 Multi Level Akses**:
  * **Super Admin**: Akses penuh ke seluruh fitur & WA Sender.
  * **Admin**: Kelola data siswa, guru, kelas, dan jurusan.
  * **Guru / Wali Kelas**: Scan QR kelas & monitoring presensi siswa.
  * **Siswa**: Melihat riwayat kehadiran pribadi.
* **🔒 Self-Hosted & Bebas Biaya Langganan Server**: Bisa diinstall di server lokal sekolah (Local LAN) maupun Cloud Server (VPS).

---

## 2. 💰 PAKET HARGA & STRATEGI KOMERSIAL

Anda dapat menggunakan **2 Skema Bisnis** berikut:

### Skema A: Sekali Bayar (Jual Putus / Self-Hosted)
* **Harga**: **Rp 2.500.000 – Rp 5.000.000** per sekolah.
* **Termasuk**:
  * Source code & instalasi di server sekolah (PC Windows / CasaOS / VPS).
  * Pelatihan operator sekolah (1-2 jam).
  * Garansi / maintenance gratis 1-3 bulan pertama.

### Skema B: Langganan Bulanan / Tahunan (SaaS Service)
* **Harga**: **Rp 300.000 – Rp 750.000** / bulan per sekolah.
* **Keuntungan**: Pendapatan berulang (recurring income) setiap bulan/tahun.

### 💡 Pendapatan Tambahan (Upselling Options):
* **Jasa Cetak Kartu Pelajar PVC**: Rp 5.000 – Rp 10.000 / siswa (Modal Rp 1.500/kartu, untung hingga 400%).
* **Integrasi Gateway WhatsApp (Fonnte)**: Sediakan saldo WA / nomor sender khusus sekolah.

---

## 3. 🛠️ PANDUAN INSTALASI 1-KLIK (UNTUK PEMBELI)

Sistem sudah dilengkapi dengan **Script Installer Otomatis Interaktif**.

### A. Instalasi di Linux / VPS / CasaOS (Rekomendasi)

1. Upload seluruh folder aplikasi ke server pembeli (misal ke `/DATA/AppData/absensi_qrcode` atau `/home/ubuntu/absensi`).
2. Masuk ke terminal server dan jalankan perintah 1-klik:
   ```bash
   cd /DATA/AppData/absensi_qrcode
   chmod +x install.sh update.sh backup.sh
   bash install.sh
   ```
3. Script akan meminta input interaktif:
   * Nama Sekolah
   * URL/Domain Sekolah (contoh: `https://absensi.smk.sch.id` atau `http://192.168.1.100:8585`)
   * Port Aplikasi (default: 8585)
   * Email & Password Admin Kustom
4. Selesai! Aplikasi langsung siap digunakan.

---

### B. Instalasi di Windows (Menggunakan Docker Desktop)

1. Pastikan **Docker Desktop** sudah terinstall di PC Windows server sekolah.
2. Buka `Command Prompt` (CMD) atau PowerShell di folder project.
3. Jalankan:
   ```cmd
   deploy-push.bat "Initial Setup"
   ```
4. Buka browser: `http://localhost:8585`

---

## 4. 🚀 ALUR PENGGUNAAN APLIKASI (QUICK START)

Setelah instalasi selesai, ikuti langkah mudah ini:

1. **Login Super Admin**:
   * Buka URL aplikasi ➔ Login menggunakan email & password admin yang dibuat saat instalasi.
2. **Koneksikan WA Gateway**:
   * Masuk ke menu **WA Sender** ➔ Tambahkan Token Fonnte ([fonnte.com](https://fonnte.com)).
3. **Persiapkan Data Master**:
   * Tambah Data **Jurusan**, **Tahun Ajaran**, dan **Kelas**.
   * Hubungkan **Kelas** dengan **WA Sender**.
4. **Input Data Siswa & Cetak Kartu**:
   * Masuk ke Data Siswa ➔ Import via Excel atau Tambah Manual (sertakan No. WA Orang Tua).
   * Klik tombol **Cetak Batch Kartu QR** untuk mengunduh PDF kartu pelajar siap cetak.
5. **Mulai Scan Absensi**:
   * Wali kelas/Guru login ➔ Masuk menu **Scan Absensi** ➔ Pilih Kelas ➔ Arahkan QR Code siswa ke kamera.
   * Notifikasi WA langsung terkirim otomatis ke HP Orang Tua!

---

## 5. 🔄 PEMELIHARAAN & UPDATE SYSTEM

### Update Aplikasi (Jika Ada Fitur Baru):
Pembeli cukup menjalankan 1 perintah update tanpa menghapus data yang ada:
```bash
bash update.sh
```

### Backup Data (Database + File):
Untuk mengamankan data sekolah secara rutin:
```bash
bash backup.sh
```
*(File backup `.tar.gz` akan otomatis tersimpan di folder `backups/`)*

---
*Dibuat untuk Lisensi Komersial & Distribusi Klien.*
