# 📱 Sistem Absensi QR Code & Notifikasi WhatsApp (MHC)
> **Developer:** qpawdeveloper  
> **Official License:** Commercial & School Edition

Sistem Absensi Sekolah Berbasis QR Code dengan Notifikasi WhatsApp Real-Time ke Orang Tua Siswa, Rekapitulasi Laporan Automatic, Cetak Batch Kartu QR Pelajar PDF, dan Multi-Sender per Kelas.

---

## 🌟 Fitur Utama
* **⚡ Web-based QR Code Scanner**: Mendukung scan cepat via Kamera HP, Tablet, Laptop, maupun USB QR Scanner.
* **📱 WhatsApp Gateway Real-Time**: Notifikasi otomatis ke orang tua begitu siswa melakukan presensi (Hadir / Terlambat / Alpha / Izin).
* **🪪 Cetak Batch Kartu Pelajar (PDF)**: Sekali klik untuk generate dan cetak kartu pelajar berpola QR Code per kelas.
* **📊 Laporan & Rekapitulasi**: Export laporan kehadiran per kelas/siswa ke PDF & Excel.
* **⏰ Pengaturan Jam Absensi**: Batas masuk, toleransi keterlambatan, dan jam kerja per kelas.
* **🤖 Android Mobile App API**: RESTful API lengkap (Sanctum Auth, QR Scanner, Siswa Profile, Admin Dashboard, Push Notification FCM).
* **🐳 Dockerized Deployment**: Siap di-deploy dengan Docker Compose & script 1-klik (`install.sh`).

---

## 💰 Paket Harga Penjualan (Commercial Pricing)

Aplikasi ini dapat dikomersialkan ke sekolah-sekolah (SD, SMP, SMA, SMK) dengan skema:

| Paket Penjualan | Harga Lisensi | Keunggulan & Layanan |
| :--- | :--- | :--- |
| **🥉 Paket LITE** | **Rp 1.950.000** *(Sekali Bayar)* | s.d 300 Siswa, Local Server, 1 WA Sender, Online Training |
| **🥈 Paket STANDARD** | **Rp 3.500.000** *(Sekali Bayar)* | s.d 1.000 Siswa, VPS/Local, Multi-Sender WA per Kelas, Support 6 Bulan |
| **🥇 Paket ENTERPRISE** | **Rp 6.500.000** *(Sekali Bayar)* | Unlimited Siswa, Free Hardware Scanner, 100 Kartu PVC, Support 1 Tahun |
| **🔄 Langganan SaaS** | **Rp 249rb – Rp 749rb** / bulan | Tanpa server sekolah, Managed Cloud Server, Free Update |

---

## 📖 Dokumentasi Lengkap

👉 **[PANDUAN KOMERSIALISASI, PENJUALAN & PAKET HARGA](file:///c:/xampp/htdocs/absensi_qrcode/PANDUAN_PENJUALAN_DAN_INSTALASI.md)**
*(Berisi analisis harga pasar, draf proposal ke Sekolah, proyeksi profit vendor, dan skema paket).*

👉 **[PANDUAN INSTALASI & INTEGRASI WHATSAPP GATEWAY](file:///c:/xampp/htdocs/absensi_qrcode/PANDUAN_INSTALASI_GATEWAY.md)**
*(Berisi langkah-langkah teknis instalasi Docker / XAMPP dan integrasi WhatsApp Gateway API).*

👉 **[DOKUMENTASI RESTFUL API APLIKASI ANDROID](file:///c:/xampp/htdocs/absensi_qrcode/DOKUMENTASI_API_ANDROID.md)**
*(Spesifikasi lengkap REST API, payload JSON, dan contoh implementasi Kotlin Retrofit).*

👉 **[PROMPT MASTER PEMBUATAN APLIKASI ANDROID MODERN](file:///c:/xampp/htdocs/absensi_qrcode/PROMPT_PEMBUATAN_APLIKASI_ANDROID.md)**
*(Prompt AI siap pakai untuk membangun aplikasi mobile Android tampilan modern).*

👉 **[KODE SUMBER KOTLIN ANDROID NATIVE APP](file:///c:/xampp/htdocs/absensi_qrcode/KOTLIN_ANDROID_APP_CODE.md)**
*(Source Code & Arsitektur Lengkap Android Native Kotlin + Jetpack Compose + Material 3 + Retrofit 2 + CameraX + ML Kit).*

---

## ⚡ Quick Start 1-Klik (Docker)

```bash
# Clone / upload project ke server
cd /DATA/AppData/absensi_qrcode

# Berikan izin eksekusi
chmod +x install.sh update.sh backup.sh

# Jalankan installer otomatis
./install.sh
```

---
*Developed by **qpawdeveloper** — Hak Cipta & Lisensi Komersial Sekolah.*
