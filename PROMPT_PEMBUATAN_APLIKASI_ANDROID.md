# 🚀 PROMPT MASTER & DOKUMENTASI API LENGKAP: APLIKASI ANDROID (ABSENSI QR CODE & WA)
> **Developer:** qpawdeveloper  
> **Production Base URL:** `https://absensi.smkmuthiaharapanclk.com`  
> **Backend Framework:** Laravel 12 + Sanctum Bearer Token Auth  
> **Dokumen:** All-in-One Prompt Master & RESTful API Complete Documentation

Dokumen ini merupakan **Prompt Master All-in-One** yang menggabungkan panduan desain UI/UX Android modern, arsitektur modul, dan **Spesifikasi RESTful API Lengkap (JSON Payload & Response)** yang terkonfigurasi untuk server CasaOS sekolah (**`https://absensi.smkmuthiaharapanclk.com`**).

---

## 📋 SALIN SELURUH PROMPT MASTER DI BAWAH INI:

```text
Act as a Senior Mobile Developer & Expert UI/UX Designer.

Build a complete, modern, state-of-the-art Android Application for "Sistem Absensi QR Code & Notifikasi WhatsApp Sekolah" developed by qpawdeveloper.

Production Server Base URL:
https://absensi.smkmuthiaharapanclk.com

Preferred Tech Stack:
- Native Android (Kotlin + Jetpack Compose + Material 3) OR Flutter (Dart)
- Networking: Retrofit 2 + OkHttp3 + Coroutines / Flow
- QR Scanning Engine: CameraX + Google ML Kit Barcode Scanning
- Local Storage & Session: EncryptedSharedPreferences (DataStore) + ViewModel + StateFlow
- Image Loading: Coil / CachedNetworkImage
- UI Animations: Lottie Animations + Accompanist Navigation Animation

=================================================================================
🎨 1. DESIGN AESTHETICS & UI/UX REQUIREMENTS
=================================================================================
1. **Modern Premium Design System**:
   - Palette: Sleek Dark Mode & Vibrant Light Mode.
   - Primary Colors: Deep Royal Indigo (`#4F46E5`), Emerald Green (`#10B981`), Crimson Amber (`#F59E0B`), Dark Charcoal (`#0F172A`).
   - Card Styling: Glassmorphism effect, subtle border gradients, smooth elevation shadows, soft rounded corners (16dp - 24dp).
   - Typography: Google Fonts Inter / Outfit (Clean, bold headings, legible body text).
   - Haptic Feedback: Getar haptic & Lottie animation popup saat scan QR code berhasil.

2. **Role-Based Dynamic User Interface**:
   Aplikasi mendukung 3 Role (Guru, Siswa, Admin) dengan antarmuka yang disesuaikan secara otomatis setelah Login.

=================================================================================
📱 2. FEATURE & MODULE SPECIFICATIONS
=================================================================================

#### MODULE 1: AUTENTIKASI & CONFIG
- **Server URL Setup Screen**: Default Base URL ke `https://absensi.smkmuthiaharapanclk.com` dengan tombol "Tes Koneksi Server".
- **Login Screen**: Minimalist modern login form dengan toggle password visibility, logo sekolah, dan auto-save session.
- **Token Manager**: Simpan Sanctum Bearer Token di Encrypted Storage. Otomatis redirect ke Login jika server merespons 401 Unauthorized.
- **Change Password & Profile**: Fitur ubah password & profil user.

#### MODULE 2: GURU / WALI KELAS
- **Camera QR Scanner (Real-time)**:
  - Overlay scanner bundar/persegi dengan garis laser animasi scan.
  - Saat QR Code siswa terdeteksi: Otomatis panggil API `POST https://absensi.smkmuthiaharapanclk.com/api/guru/absensi/scan`.
  - Tampilkan BottomSheet / Card Dialog Popup hasil scan berisi Foto Siswa, Nama, NIS, Kelas, dan Badge Status ("HADIR TEPAT WAKTU" / "TERLAMBAT").
  - Sound effect bip & getar haptic saat scan berhasil.
- **Presensi Manual**: Form cepat input presensi manual jika siswa tidak membawa kartu QR (Pilih Siswa -> Status: Hadir/Izin/Sakit/Alpha -> Simpan).
- **Edit & Hapus Absensi**: Fitur untuk Guru/Wali Kelas mengubah status/keterangan absensi siswa (`PUT /api/guru/absensi/{id}`) atau menghapus record absensi (`DELETE /api/guru/absensi/{id}`) langsung dari HP.
- **Penilaian & Batch Entri Nilai Siswa**:
  - Daftar tugas/materi per kelas (`GET /api/guru/penilaian`).
  - Detail tugas & form input batch nilai siswa (`GET /api/guru/penilaian/{id}`) dilengkapi status Tuntas/Remidi dan field catatan feedback guru.
  - Simpan Batch Nilai 1-Klik (`POST /api/guru/penilaian/{id}/nilai-batch`).
- **Dashboard Kelas & Real-Time Counter**: Ringkasan jumlah siswa Hadir, Terlambat, Izin, Sakit, dan Belum Absen dalam kelas diampu.
- **Rekap & Filter Absensi**: Filter rekap absensi per tanggal.

#### MODULE 3: SISWA
- **Dynamic Brightness QR Screen**:
  - Saat tab QR dibuka, layar HP otomatis menaikkan brightness ke 100% agar mudah di-scan kamera scanner.
  - Menampilkan QR Code siswa yang besar, jelas, dilengkapi nama, NIS, dan tombol "Refresh QR Code".
- **Live Status Card Hari Ini**: Banner status kehadiran hari ini ("Anda Sudah Hadir Pukul 06:55 WIB").
- **Riwayat Kehadiran 30 Hari**: Card list riwayat absensi dengan indikator warna status.
- **Statistik Kehadiran**: Circular Progress Bar persentase kehadiran bulan ini & total hari masuk.
- **Nilai & Evaluasi Harian**:
  - Transkrip nilai tugas, bab materi, dan ulangan (`GET /api/siswa/nilai`).
  - Ringkasan statistik di header screen: Rata-Rata Nilai, Total Tugas Tuntas (&ge;75), Total Remidi (&lt;75), Tertinggi, dan Terendah.
  - Search bar cepat pencarian berdasarkan Mapel, Bab Materi, atau Judul Tugas.
  - Card item nilai dilengkapi Badge Status ("Tuntas (B)" / "Remidi (D)"), KKM, dan Catatan/Feedback dari Guru.
- **Pengajuan Surat Izin / Sakit**: Form pengajuan izin/sakit dengan upload foto bukti surat dokter langsung dari Kamera/Galeri HP.

#### MODULE 4: ADMIN / SUPER ADMIN (FULL MOBILE SUITE)
- **Executive Dashboard**: Ringkasan total siswa, total kelas, chart 7 hari terakhir, dan statistik absensi global sekolah hari ini.
- **Monitoring & Kelola Data Siswa & Guru**: Search bar cepat cari data siswa/guru, lihat profil, NIS, NIP, dan wali kelas.
- **WA Sender Status Monitor**: Status live device WA Sender (`● Aktif` / `● Terputus`), nomor HP sender, dan jumlah kelas terhubung.
- **Log WhatsApp Real-time**: Riwayat notifikasi WA terkirim ke orang tua beserta status pengiriman.
- **Full Absensi Control**: Super Admin memiliki akses penuh untuk Scan QR, Input Manual, Edit Absensi, dan Hapus Absensi.

=================================================================================
🔌 3. COMPLETE RESTFUL API SPECIFICATION & JSON PAYLOADS
=================================================================================

Server Base URL: https://absensi.smkmuthiaharapanclk.com

Base Headers Required for Authenticated Requests:
Authorization: Bearer <SANCTUM_TOKEN>
Content-Type: application/json
Accept: application/json

---------------------------------------------------------------------------------
A. AUTHENTICATION ENDPOINTS
---------------------------------------------------------------------------------
1. Login User (POST /api/auth/login)
   Request Body:
   {
     "email": "guru@sekolah.com",
     "password": "Password123!",
     "device_name": "Samsung Galaxy A54"
   }
   Response 200 OK:
   {
     "success": true,
     "token": "1|abc123xyzTokenSanctum...",
     "user": {
       "id": 5,
       "name": "Budi Santoso, S.Pd.",
       "email": "guru@sekolah.com",
       "role": "guru",
       "roles": ["guru"],
       "guru": {
         "id": 2,
         "nip": "198501012010011001",
         "nama": "Budi Santoso, S.Pd.",
         "foto": "https://absensi.smkmuthiaharapanclk.com/storage/guru/budi.jpg"
       }
     }
   }
   Response 401 Unauthorized:
   {
     "success": false,
     "message": "Email atau password salah."
   }

2. Get User Profile (GET /api/auth/me)
   Response 200 OK:
   {
     "success": true,
     "user": {
       "id": 5,
       "name": "Budi Santoso",
       "email": "guru@sekolah.com",
       "role": "guru"
     }
   }

3. Change Password (POST /api/auth/change-password)
   Request Body:
   {
     "current_password": "PasswordLama123",
     "new_password": "PasswordBaru456",
     "new_password_confirmation": "PasswordBaru456"
   }
   Response 200 OK:
   {
     "success": true,
     "message": "Password berhasil diperbarui."
   }

4. Logout (POST /api/auth/logout)
   Response 200 OK:
   {
     "success": true,
     "message": "Berhasil logout."
   }

---------------------------------------------------------------------------------
B. GURU / WALI KELAS ENDPOINTS
---------------------------------------------------------------------------------
1. List Kelas Diampu (GET /api/guru/kelas)
   Response 200 OK:
   {
     "success": true,
     "data": [
       {
         "id": 1,
         "nama": "X RPL 1",
         "jurusan": "Rekayasa Perangkat Lunak",
         "total_siswa": 36
       }
     ]
   }

2. Scan QR Code Absensi (POST /api/guru/absensi/scan)
   Request Body:
   {
     "qr_token": "token_qr_32_karakter_siswa",
     "kelas_id": 1
   }
   Response 200 OK:
   {
     "success": true,
     "message": "Absensi BERHASIL! Ahmad Rizky tercatat HADIR (Jam 06:55:10). Notifikasi WA terkirim.",
     "siswa": {
       "nama": "Ahmad Rizky",
       "nis": "20241001",
       "kelas": "X RPL 1",
       "foto_url": "https://absensi.smkmuthiaharapanclk.com/storage/siswa/photo.jpg"
     },
     "absensi": {
       "status": "hadir",
       "status_label": "Hadir Tepat Waktu",
       "status_color": "green",
       "jam_scan": "06:55:10"
     }
   }

3. Presensi Manual Siswa (POST /api/guru/absensi/manual)
   Request Body:
   {
     "siswa_id": 12,
     "kelas_id": 1,
     "status": "hadir",
     "keterangan": "Kartu QR tertinggal",
     "tanggal": "2026-08-02"
   }
   Response 200 OK:
   {
     "success": true,
     "message": "Presensi manual Ahmad Rizky (HADIR) berhasil disimpan.",
     "absensi": {
       "id": 105,
       "siswa_nama": "Ahmad Rizky",
       "status": "hadir",
       "status_label": "Hadir",
       "jam_scan": "07:10:00",
       "tanggal": "2026-08-02"
     }
   }

4. Rekap Absensi Kelas (GET /api/guru/absensi/rekap/{kelas_id}?tanggal=YYYY-MM-DD)
   Response 200 OK:
   {
     "success": true,
     "kelas": {"id": 1, "nama": "X RPL 1"},
     "tanggal": "2026-08-02",
     "absensi": [
       {
         "id": 101,
         "nama": "Ahmad Rizky",
         "nis": "20241001",
         "status": "hadir",
         "jam_scan": "06:55:10"
       }
     ],
     "belum_absen": [],
     "stats": {
       "hadir": 30,
       "terlambat": 4,
       "izin": 1,
       "sakit": 1,
       "alpha": 0
     }
   }

5. Detail Penilaian & Entri Nilai Siswa (GET /api/guru/penilaian/{id})
   Response 200 OK:
   {
     "success": true,
     "penilaian": {
       "id": 10,
       "kelas_id": 1,
       "kelas": "X RPL 1",
       "guru_nama": "Budi Santoso, S.Pd.",
       "mata_pelajaran": "Pemrograman Web",
       "kode_mapel": "PPLG-WEB",
       "bab_materi": "Bab 1 - Dasar HTML & CSS",
       "judul_tugas": "Tugas 1 - Layout Landing Page",
       "jenis": "tugas",
       "jenis_label": "Tugas Harian",
       "tanggal": "2026-08-03",
       "tanggal_formatted": "Senin, 03 Agustus 2026",
       "kkm": 75
     },
     "ringkasan": {
       "total_siswa": 32,
       "sudah_dinilai": 30,
       "tuntas_count": 28,
       "remidi_count": 2,
       "belum_dinilai_count": 2,
       "rata_rata": 84.5
     },
     "nilai_siswa": [
       {
         "id": 101,
         "siswa_id": 12,
         "nama_siswa": "Ahmad Rizky",
         "nis": "20241001",
         "foto_url": "https://absensi.smkmuthiaharapanclk.com/storage/siswa/photo.jpg",
         "nilai": 88.5,
         "nilai_formatted": "88.5",
         "kkm": 75,
         "is_tuntas": true,
         "predikat": "B",
         "catatan_guru": "Bagus, layout sangat rapi",
         "status": "Tuntas",
         "status_color": "#10b981"
       }
     ]
   }

6. Simpan Batch Nilai Siswa (POST /api/guru/penilaian/{id}/nilai-batch)
   Request Body (Format Array List):
   {
     "items": [
       {
         "siswa_id": 12,
         "nilai": 88.5,
         "catatan_guru": "Sangat rapi"
       },
       {
         "siswa_id": 13,
         "nilai": 75.0,
         "catatan_guru": "Cukup baik"
       }
     ]
   }
   Response 200 OK:
   {
     "success": true,
     "message": "Nilai seluruh siswa berhasil diperbarui."
   }

---------------------------------------------------------------------------------
C. SISWA ENDPOINTS
---------------------------------------------------------------------------------
1. Profil & Display QR Code Siswa (GET /api/siswa/profile)
   Response 200 OK:
   {
     "success": true,
     "siswa": {
       "id": 12,
       "nis": "20241001",
       "nama": "Ahmad Rizky",
       "foto_url": "https://absensi.smkmuthiaharapanclk.com/storage/siswa/photo.jpg",
       "qr_token": "8f7a9b0c1d2e3f4a5b6c7d8e9f0a1b2c",
       "kelas": {
         "id": 1,
         "nama": "X RPL 1",
         "jurusan": "Rekayasa Perangkat Lunak"
       }
     },
     "absensi_hari_ini": {
       "status": "hadir",
       "status_label": "Hadir",
       "jam_scan": "06:55:10",
       "tanggal": "2026-08-02"
     }
   }

2. Refresh QR Code Token (POST /api/siswa/qr-refresh)
   Response 200 OK:
   {
     "success": true,
     "message": "QR Token berhasil diperbarui.",
     "qr_token": "new_generated_32_char_token"
   }

3. Riwayat Absensi Siswa (GET /api/siswa/absensi?bulan=YYYY-MM)
   Response 200 OK:
   {
     "success": true,
     "riwayat": [
       {
         "id": 101,
         "tanggal": "2026-08-02",
         "tanggal_label": "Minggu, 02 Agustus 2026",
         "jam_scan": "06:55:10",
         "status": "hadir",
         "status_label": "Hadir",
         "status_color": "green"
       }
     ]
   }

4. Nilai & Evaluasi Harian Siswa (GET /api/siswa/nilai?search=...&jenis=...&mapel_id=...)
   Response 200 OK:
   {
     "success": true,
     "rata_rata": 86.5,
     "ringkasan": {
       "rata_rata": 86.5,
       "total_evaluasi": 12,
       "total_tuntas": 10,
       "total_remidi": 2,
       "total_belum_dinilai": 0,
       "tertinggi": 98.0,
       "terendah": 65.0,
       "kkm_default": 75
     },
     "data": [
       {
         "id": 101,
         "tugas_materi_id": 10,
         "mata_pelajaran": "Pemrograman Web",
         "kode_mapel": "PPLG-WEB",
         "guru_nama": "Budi Santoso, S.Pd.",
         "bab_materi": "Bab 1 - Dasar HTML & CSS",
         "judul_tugas": "Tugas 1 - Layout Landing Page",
         "jenis": "tugas",
         "jenis_label": "Tugas Harian",
         "tanggal": "2026-08-03",
         "tanggal_formatted": "Senin, 03 Agustus 2026",
         "nilai": 88.5,
         "nilai_formatted": "88.5",
         "kkm": 75,
         "is_tuntas": true,
         "predikat": "B",
         "status": "Tuntas",
         "status_color": "#10b981",
         "catatan_guru": "Sangat rapi, struktur HTML valid"
       }
     ]
   }

5. Statistik Kehadiran Siswa (GET /api/siswa/absensi/stats)
   Response 200 OK:
   {
     "success": true,
     "bulan_ini": {"hadir": 20, "terlambat": 2, "izin": 1, "sakit": 0, "alpha": 0},
     "total": {"hadir": 180, "terlambat": 5, "izin": 2, "sakit": 1, "alpha": 0},
     "pct_hadir": 98.4,
     "total_hari": 188
   }

6. Pengajuan Izin / Sakit (POST /api/siswa/izin-sakit)
   Header: Content-Type: multipart/form-data
   Form Data Params:
   - status: "sakit" (atau "izin")
   - keterangan: "Demam tinggi dan berobat ke dokter"
   - bukti_foto: [File Gambar Surat Dokter] (Optional)
   Response 200 OK:
   {
     "success": true,
     "message": "Pengajuan SAKIT berhasil dikirim.",
     "absensi": {
       "id": 110,
       "status": "sakit",
       "status_label": "Sakit",
       "keterangan": "Demam tinggi (Bukti: storage/izin_sakit/abc.jpg)",
       "tanggal": "2026-08-02"
     }
   }

---------------------------------------------------------------------------------
D. ADMIN ENDPOINTS
---------------------------------------------------------------------------------
1. Admin Dashboard (GET /api/admin/dashboard)
   Response 200 OK:
   {
     "success": true,
     "tanggal": "Minggu, 02 Agustus 2026",
     "ringkasan": {
       "total_siswa": 720,
       "total_kelas": 24,
       "hadir": 680,
       "terlambat": 25,
       "izin": 5,
       "sakit": 3,
       "alpha": 7,
       "belum_absen": 0
     },
     "per_kelas": [...],
     "chart_7_hari": [...]
   }

2. Status Device WA Sender (GET /api/admin/wa-sender)
   Response 200 OK:
   {
     "success": true,
     "data": [
       {
         "id": 1,
         "name": "WA Official Sekolah",
         "phone": "6281234567890",
         "status": "aktif",
         "status_color": "#22c55e",
         "kelas_count": 5
       }
     ]
   }

=================================================================================
💻 4. KOTLIN RETROFIT CODE EXAMPLE
=================================================================================
// ApiClient.kt
object ApiClient {
    private const val BASE_URL = "https://absensi.smkmuthiaharapanclk.com/"
    
    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}

Buatkan arsitektur project yang rapi (Clean Architecture / MVVM), ikuti best practices, dan pastikan kode bebas dari error/bugs!
```

---

*ALL-IN-ONE PROMPT MASTER — Developed by **qpawdeveloper**.*


---

*ALL-IN-ONE PROMPT MASTER — Developed by **qpawdeveloper**.*
