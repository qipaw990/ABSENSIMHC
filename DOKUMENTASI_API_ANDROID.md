# 📱 DOKUMENTASI LENGKAP RESTFUL API APLIKASI MOBILE ANDROID
> **Aplikasi:** Sistem Absensi QR Code & WA (MHC)  
> **Developer:** qpawdeveloper  
> **Auth Method:** Laravel Sanctum (`Bearer Token`)  
> **Format Request & Response:** JSON (`Content-Type: application/json` & `Accept: application/json`)  
> **Terakhir Diperbarui:** Agustus 2026

Dokumen ini berisi spesifikasi teknis lengkap untuk seluruh endpoint **RESTful API** yang tersedia pada backend sistem absensi, dirancang untuk diintegrasikan dengan aplikasi Android (Native Kotlin/Java, Flutter, React Native).

---

## 📑 DAFTAR ISI
1. [Standar Request & Headers](#1-standar-request--headers)
2. [Check Server & Health (`/api`)](#2-check-server--health-api)
3. [Modul Autentikasi (`/api/auth`)](#3-modul-autentikasi-apiauth)
4. [Modul Guru / Wali Kelas (`/api/guru`)](#4-modul-guru--wali-kelas-apiguru)
5. [Modul Siswa (`/api/siswa`)](#5-modul-siswa-apisiswa)
6. [Modul Admin (`/api/admin`)](#6-modul-admin-apiadmin)
7. [Format Error & Status Code](#7-format-error--status-code)
8. [Contoh Code Client Android (Kotlin + Retrofit)](#8-contoh-code-client-android-kotlin--retrofit)

---

## 1. 🌐 STANDAR REQUEST & HEADERS

* **Base URL**: `http://IP_SERVER:8585` atau `https://absensi.sekolah.sch.id`
* **Headers Publik (Tanpa Token)**:
  ```http
  Content-Type: application/json
  Accept: application/json
  ```
* **Headers Terproteksi (Dengan Bearer Token)**:
  ```http
  Authorization: Bearer <TOKEN_SANCTUM>
  Content-Type: application/json
  Accept: application/json
  ```

---

## 2. 🟢 CHECK SERVER & HEALTH (`/api`)

### 1. Root API Check (GET `/api`)
Mengecek status ketersediaan service API backend.

* **Headers**: Tidak memerlukan autentikasi token.
* **Response 200 OK**:
  ```json
  {
    "status": "online",
    "service": "RESTful API Sistem Absensi MHC",
    "developer": "qpawdeveloper",
    "version": "1.0.0",
    "endpoints": {
      "login": "POST /api/auth/login",
      "health": "GET /up"
    },
    "timestamp": "2026-08-04T16:00:00+07:00"
  }
  ```

---

## 3. 🔑 MODUL AUTENTIKASI (`/api/auth`)

### 1. Login User (POST `/api/auth/login`)
Melakukan autentikasi akun user (Guru, Siswa, Admin/Super Admin) untuk mendapatkan Sanctum Bearer Token.

* **Headers**: `Content-Type: application/json`
* **Request Body**:
  ```json
  {
    "email": "guru@sekolah.sch.id",
    "password": "password123",
    "device_name": "Samsung Galaxy A54"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "token": "1|abc123xyzTokenSanctum...",
    "user": {
      "id": 5,
      "name": "Budi Santoso, S.Pd.",
      "email": "guru@sekolah.sch.id",
      "role": "guru",
      "roles": ["guru"],
      "guru": {
        "id": 2,
        "nip": "198501012010011001",
        "nama": "Budi Santoso, S.Pd.",
        "foto": "http://IP_SERVER:8585/storage/guru/budi.jpg"
      }
    }
  }
  ```
  *(Catatan: Jika user ber-role `siswa`, properti `siswa` akan terisi otomatis beserta detail kelas dan `qr_token`)*.

---

### 2. Check Profile Login (GET `/api/auth/me`)
Mendapatkan informasi profil lengkap user yang sedang aktif berdasarkan Bearer Token.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "user": {
      "id": 5,
      "name": "Budi Santoso, S.Pd.",
      "email": "guru@sekolah.sch.id",
      "role": "guru",
      "roles": ["guru"],
      "guru": {
        "id": 2,
        "nip": "198501012010011001",
        "nama": "Budi Santoso, S.Pd.",
        "foto": "http://IP_SERVER:8585/storage/guru/budi.jpg"
      }
    }
  }
  ```

---

### 3. Ubah Password (POST `/api/auth/change-password`)
Mengubah password user dari aplikasi Android.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "current_password": "passwordLama123",
    "new_password": "passwordBaru456",
    "new_password_confirmation": "passwordBaru456"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Password berhasil diperbarui."
  }
  ```
* **Response 422 Unprocessable Entity (Password lama tidak cocok)**:
  ```json
  {
    "success": false,
    "message": "Password saat ini tidak cocok."
  }
  ```

---

### 4. Update FCM Device Token (POST `/api/auth/fcm-token`)
Mendaftarkan atau memperbarui Firebase Cloud Messaging (FCM) token HP Android untuk menerima Notifikasi Push.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "fcm_token": "eXamPleFcmToken1234567890abcdef..."
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Device token FCM berhasil diperbarui."
  }
  ```

---

### 5. Logout User (POST `/api/auth/logout`)
Mencabut (`revoke`) Bearer Token saat ini dan mengakhiri sesi login.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Berhasil logout."
  }
  ```

---

## 4. 👨‍🏫 MODUL GURU / WALI KELAS (`/api/guru`)
> **Akses Role**: `guru`, `admin`, `super_admin`

### 1. Daftar Kelas yang Diampu (GET `/api/guru/kelas`)
Menampilkan daftar kelas yang diampu / wali kelas guru yang login. (Jika admin/super_admin, menampilkan seluruh kelas).

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
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
  ```

---

### 2. Statistik Kelas Hari Ini (GET `/api/guru/kelas/{id}/stats`)
Mendapatkan statistik absensi kelas secara real-time pada hari ini.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "kelas": {
      "id": 1,
      "nama": "X RPL 1",
      "jurusan": "Rekayasa Perangkat Lunak"
    },
    "stats": {
      "hadir": 30,
      "terlambat": 3,
      "izin": 1,
      "sakit": 1,
      "alpha": 0
    },
    "tanggal": "Selasa, 04 Agustus 2026"
  }
  ```

---

### 3. Daftar Siswa Belum Scan Hari Ini (GET `/api/guru/kelas/{id}/belum-scan`)
Mendapatkan daftar siswa yang belum melakukan scan presensi hari ini pada kelas tertentu.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "count": 2,
    "siswa": [
      {
        "id": 15,
        "nama": "Doni Setiawan",
        "nis": "20241015",
        "foto_url": "http://IP_SERVER:8585/storage/siswa/doni.jpg"
      }
    ]
  }
  ```

---

### 4. Scan QR Code Absensi Siswa (POST `/api/guru/absensi/scan`)
Memproses token QR code hasil scan kamera Android milik Guru/Wali Kelas.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "qr_token": "8f7a9b0c1d2e3f4a5b6c7d8e9f0a1b2c",
    "kelas_id": 1
  }
  ```
* **Response 200 OK (Presensi Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Absensi BERHASIL! Ahmad Rizky tercatat HADIR (Jam 06:55:10). Notifikasi WA terkirim.",
    "siswa": {
      "nama": "Ahmad Rizky",
      "nis": "20241001",
      "kelas": "X RPL 1",
      "foto_url": "http://IP_SERVER:8585/storage/siswa/ahmad.jpg"
    },
    "absensi": {
      "status": "hadir",
      "status_label": "Hadir Tepat Waktu",
      "status_color": "green",
      "jam_scan": "06:55:10"
    }
  }
  ```

---

### 5. Input Presensi Manual Siswa (POST `/api/guru/absensi/manual`)
Digunakan oleh guru jika siswa tidak membawa HP / kartu QR Code. Otomatis mengirimkan notifikasi WA ke orang tua siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "siswa_id": 12,
    "kelas_id": 1,
    "status": "hadir",
    "keterangan": "Kartu QR tertinggal",
    "tanggal": "2026-08-04"
  }
  ```
  *(Status pilihan: `hadir`, `terlambat`, `izin`, `sakit`, `alpha`)*
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Presensi manual Ahmad Rizky (HADIR) berhasil disimpan.",
    "absensi": {
      "id": 105,
      "siswa_nama": "Ahmad Rizky",
      "status": "hadir",
      "status_label": "Hadir",
      "jam_scan": "07:05:00",
      "tanggal": "2026-08-04"
    }
  }
  ```

---

### 6. Rekap Absensi Kelas per Tanggal (GET `/api/guru/absensi/rekap/{kelas_id}?tanggal=YYYY-MM-DD`)
Mendapatkan rekapitulasi data absensi siswa di kelas tertentu berdasarkan tanggal.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameter**: `tanggal` (opsional, format `YYYY-MM-DD`, default: hari ini)
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "kelas": {
      "id": 1,
      "nama": "X RPL 1"
    },
    "tanggal": "2026-08-04",
    "absensi": [
      {
        "id": 101,
        "nama": "Ahmad Rizky",
        "nis": "20241001",
        "foto_url": "http://IP_SERVER:8585/storage/siswa/ahmad.jpg",
        "status": "hadir",
        "status_label": "Hadir",
        "status_color": "green",
        "jam_scan": "06:55:10"
      }
    ],
    "belum_absen": [
      {
        "id": 15,
        "nama": "Doni Setiawan",
        "nis": "20241015"
      }
    ],
    "stats": {
      "hadir": 30,
      "terlambat": 3,
      "izin": 1,
      "sakit": 1,
      "alpha": 0
    }
  }
  ```

---

### 7. Edit Data Absensi (PUT `/api/guru/absensi/{id}`)
Mengubah status atau rincian data absensi siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "status": "terlambat",
    "jam_scan": "07:25:00",
    "keterangan": "Terlambat 25 menit (Ban meletus)"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Data absensi berhasil diperbarui.",
    "data": {
      "id": 101,
      "status": "terlambat",
      "jam_scan": "07:25:00",
      "keterangan": "Terlambat 25 menit (Ban meletus)"
    }
  }
  ```

---

### 8. Hapus Data Absensi (DELETE `/api/guru/absensi/{id}`)
Menghapus record absensi siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Data absensi berhasil dihapus."
  }
  ```

---

### 9. Jadwal Mengajar Guru (GET `/api/guru/jadwal`)
Mendapatkan jadwal jam mengajar guru yang sedang login.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "hari": "Senin",
        "jam": "07:15 - 08:45",
        "kelas": "X RPL 1",
        "mata_pelajaran": "Pemrograman Web",
        "ruangan": "Lab Komputer 1"
      }
    ]
  }
  ```

---

### 10. Master Data Mata Pelajaran untuk Guru (GET `/api/guru/mapel`)
Mendapatkan daftar master mata pelajaran untuk pilihan dropdown pada form input tugas/penilaian.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 3,
        "kode": "PPLG-WEB",
        "nama": "Pemrograman Web",
        "kelompok": "Produktif"
      }
    ]
  }
  ```

---

### 11. Opsi Form Tambah Tugas / Penilaian Baru (GET `/api/guru/penilaian/options`)
Mendapatkan seluruh opsi data (Kelas, Mapel Master Data, Jadwal Pelajaran Guru, Jenis Penilaian) sekaligus dalam 1 request HTTP untuk mengisi dropdown form "Tambah Tugas & Bab Materi Baru" pada aplikasi Android.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "data": {
      "kelas": [
        { "id": 1, "nama": "X RPL 1", "jurusan": "Rekayasa Perangkat Lunak" }
      ],
      "mapel": [
        { "id": 3, "kode": "PPLG-WEB", "nama": "Pemrograman Web" }
      ],
      "jadwal": [
        {
          "id": 5,
          "hari": "Senin",
          "jam": "07:15 - 08:45",
          "kelas_id": 1,
          "kelas_nama": "X RPL 1",
          "mata_pelajaran_id": 3,
          "mata_pelajaran": "Pemrograman Web"
        }
      ],
      "jenis": [
        { "key": "tugas", "label": "Tugas Harian" },
        { "key": "uh", "label": "Ulangan Harian (UH)" },
        { "key": "uts", "label": "Ujian Tengah Semester (UTS)" },
        { "key": "uas", "label": "Ujian Akhir Semester (UAS)" },
        { "key": "praktikum", "label": "Praktikum / Unjuk Kerja" }
      ]
    }
  }
  ```

---

### 12. Daftar Tugas & Penilaian Guru (GET `/api/guru/penilaian`)
Menampilkan daftar tugas/materi yang dibuat oleh guru beserta status pengisian nilainya.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameters (Opsional)**:
  * `kelas_id`: Filter ID kelas
  * `search`: Pencarian nama mapel / bab / judul tugas
  * `page`: Nomor halaman (Pagination 20 item per page)
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "total": 12,
    "data": [
      {
        "id": 10,
        "kelas": "X RPL 1",
        "kelas_id": 1,
        "guru_id": 2,
        "guru_nama": "Budi Santoso, S.Pd.",
        "mata_pelajaran": "Pemrograman Web",
        "mata_pelajaran_id": 3,
        "kode_mapel": "PPLG-WEB",
        "jadwal_pelajaran_id": 5,
        "bab_materi": "Bab 1 - HTML & CSS Base",
        "judul_tugas": "Tugas 1 - Responsive Layout",
        "jenis": "tugas",
        "jenis_label": "Tugas Harian",
        "tanggal": "2026-08-04",
        "tanggal_formatted": "Selasa, 04 Agustus 2026",
        "total_siswa": 36,
        "sudah_dinilai": 34
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 12
    }
  }
  ```

---

### 13. Detail & Daftar Nilai Siswa per Tugas (GET `/api/guru/penilaian/{id}`)
Mendapatkan rincian nilai seluruh siswa pada satu tugas/materi tertentu beserta ringkasan statistik (Tuntas, Remidi, Rata-rata).

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "penilaian": {
      "id": 10,
      "kelas_id": 1,
      "kelas": "X RPL 1",
      "guru_id": 2,
      "guru_nama": "Budi Santoso, S.Pd.",
      "mata_pelajaran": "Pemrograman Web",
      "bab_materi": "Bab 1 - HTML & CSS Base",
      "judul_tugas": "Tugas 1 - Responsive Layout",
      "jenis": "tugas",
      "jenis_label": "Tugas Harian",
      "tanggal": "2026-08-04",
      "keterangan": "Buat layout flexbox",
      "kkm": 75
    },
    "ringkasan": {
      "total_siswa": 36,
      "sudah_dinilai": 34,
      "tuntas_count": 30,
      "remidi_count": 4,
      "belum_dinilai_count": 2,
      "rata_rata": 84.5
    },
    "nilai_siswa": [
      {
        "id": 101,
        "siswa_id": 12,
        "nama_siswa": "Ahmad Rizky",
        "nis": "20241001",
        "foto_url": "http://IP_SERVER:8585/storage/siswa/ahmad.jpg",
        "nilai": 88.5,
        "nilai_formatted": "88.5",
        "kkm": 75,
        "is_tuntas": true,
        "predikat": "B",
        "catatan_guru": "Sangat rapi",
        "status": "Tuntas",
        "status_color": "#10b981"
      }
    ]
  }
  ```

---

### 14. Form Tambah Tugas & Bab Materi Baru (POST `/api/guru/penilaian`)
Membuat item penilaian/tugas baru dari aplikasi Android (Sesuai dengan form *Tambah Tugas & Bab Materi Baru*). Sistem akan otomatis membuat record nilai awal `0` untuk seluruh siswa di kelas tujuan.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body (JSON Payload)**:
  ```json
  {
    "kelas_id": 1,
    "mata_pelajaran_id": 3,
    "jadwal_pelajaran_id": 5,
    "bab_materi": "Bab 1 - Dasar HTML & CSS",
    "judul_tugas": "Tugas 1 - Membuat Layout Flexbox",
    "jenis": "tugas",
    "tanggal": "2026-08-04",
    "keterangan": "Petunjuk pengerjaan tugas atau deskripsi materi..."
  }
  ```
* **Deskripsi Field Input**:
  * `kelas_id` *(Integer, Wajib)*: ID Kelas Tujuan. (Jika `jadwal_pelajaran_id` diisi, field ini dapat dikosongkan karena akan terisi otomatis).
  * `mata_pelajaran_id` *(Integer, Opsional)*: ID Mata Pelajaran dari Master Data Mapel.
  * `jadwal_pelajaran_id` *(Integer, Opsional)*: ID Jadwal Pelajaran Guru. Jika diisi, sistem akan otomatis mengisi `kelas_id` & `mata_pelajaran_id`.
  * `bab_materi` *(String, Wajib)*: Bab / Topik Materi (Contoh: `"Bab 1 - Dasar HTML & CSS"`).
  * `judul_tugas` *(String, Wajib)*: Judul Tugas / Evaluasi (Contoh: `"Tugas 1 - Membuat Layout Flexbox"`).
  * `jenis` *(String, Wajib)*: Jenis Penilaian. Pilihan: `tugas` (Tugas Harian), `uh` (Ulangan Harian), `uts` (UTS), `uas` (UAS), `praktikum` (Praktikum).
  * `tanggal` *(Date YYYY-MM-DD, Wajib)*: Tanggal Penilaian / Deadline.
  * `keterangan` *(String, Opsional)*: Catatan / Deskripsi / Petunjuk Pengerjaan Tugas.

* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Tugas/Penilaian berhasil dibuat.",
    "data": {
      "id": 11,
      "kelas_id": 1,
      "mata_pelajaran_id": 3,
      "mata_pelajaran": "Pemrograman Web",
      "bab_materi": "Bab 1 - Dasar HTML & CSS",
      "judul_tugas": "Tugas 1 - Membuat Layout Flexbox",
      "jenis": "tugas",
      "tanggal": "2026-08-04",
      "keterangan": "Petunjuk pengerjaan tugas atau deskripsi materi..."
    }
  }
  ```

---

### 15. Edit Data Tugas / Penilaian (PUT `/api/guru/penilaian/{id}`)
Mengubah rincian data tugas/penilaian yang sudah pernah dibuat.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "bab_materi": "Bab 1 - Dasar HTML & CSS Grid",
    "judul_tugas": "Tugas 1 - Membuat Layout CSS Grid",
    "tanggal": "2026-08-05",
    "keterangan": "Perubahan petunjuk tugas menggunakan CSS Grid"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Tugas/Penilaian berhasil diperbarui.",
    "data": {
      "id": 11,
      "judul_tugas": "Tugas 1 - Membuat Layout CSS Grid"
    }
  }
  ```

---

### 16. Hapus Data Tugas / Penilaian (DELETE `/api/guru/penilaian/{id}`)
Menghapus item tugas/penilaian beserta seluruh data nilai siswa di dalamnya.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Tugas/Penilaian dan seluruh nilai siswa terkait berhasil dihapus."
  }
  ```

---

### 17. Simpan Batch Nilai Siswa (POST `/api/guru/penilaian/{id}/nilai-batch`)
Menginput/memperbarui nilai seluruh siswa sekaligus dalam 1 request HTTP. Mendukung 2 pilihan format JSON payload (Format List atau Format Keyed Object).

* **Headers**: `Authorization: Bearer <token>`
* **Opsi Request Body A (Format List Objects)**:
  ```json
  {
    "items": [
      { "siswa_id": 12, "nilai": 88.5, "catatan_guru": "Sangat baik" },
      { "nilai_id": 102, "nilai": 70.0, "catatan_guru": "Perlu remidi flexbox" }
    ]
  }
  ```
* **Opsi Request Body B (Format Keyed Dictionary)**:
  ```json
  {
    "nilai": {
      "101": 88.5,
      "102": 70.0
    },
    "catatan_guru": {
      "101": "Sangat baik",
      "102": "Perlu remidi flexbox"
    }
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Nilai seluruh siswa berhasil diperbarui."
  }
  ```


---

## 5. 🎓 MODUL SISWA (`/api/siswa`)
> **Akses Role**: `siswa`

### 1. Profil & Display QR Code Siswa (GET `/api/siswa/profile`)
Layar utama aplikasi Android milik Siswa untuk menampilkan QR Code digital yang siap discan oleh Guru/Wali Kelas.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "siswa": {
      "id": 12,
      "nis": "20241001",
      "nisn": "0051234567",
      "nama": "Ahmad Rizky",
      "foto_url": "http://IP_SERVER:8585/storage/siswa/ahmad.jpg",
      "qr_token": "8f7a9b0c1d2e3f4a5b6c7d8e9f0a1b2c",
      "qr_is_active": true,
      "kelas": {
        "id": 1,
        "nama": "X RPL 1",
        "jurusan": "Rekayasa Perangkat Lunak"
      }
    },
    "absensi_hari_ini": {
      "status": "hadir",
      "status_label": "Hadir Tepat Waktu",
      "status_color": "green",
      "jam_scan": "06:55:10",
      "tanggal": "2026-08-04"
    }
  }
  ```

---

### 2. Regenerate / Refresh QR Token (POST `/api/siswa/qr-refresh`)
Memperbarui string token QR Code siswa jika terjadi kebocoran token atau untuk alasan keamanan.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "QR Token berhasil diperbarui.",
    "qr_token": "9x8y7z6a5b4c3d2e1f0g9h8i7j6k5l4m"
  }
  ```

---

### 3. Riwayat Absensi Siswa (GET `/api/siswa/absensi`)
Melihat daftar riwayat kehadiran siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameter**: `bulan` (opsional, contoh: `bulan=2026-08`, jika kosong default 30 hari terakhir)
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "periode": {
      "mulai": "2026-08-01",
      "selesai": "2026-08-31"
    },
    "riwayat": [
      {
        "id": 101,
        "tanggal": "2026-08-04",
        "tanggal_label": "Selasa, 04 Agustus 2026",
        "jam_scan": "06:55:10",
        "status": "hadir",
        "status_label": "Hadir",
        "status_color": "green",
        "keterangan": "-"
      }
    ]
  }
  ```

---

### 4. Statistik Kehadiran Siswa (GET `/api/siswa/absensi/stats`)
Mendapatkan rekap akumulasi kehadiran bulan berjalan & keseluruhan tahun ajaran beserta persentase kehadiran (`pct_hadir`).

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "bulan_ini": {
      "hadir": 4,
      "terlambat": 0,
      "izin": 0,
      "sakit": 0,
      "alpha": 0
    },
    "total": {
      "hadir": 85,
      "terlambat": 2,
      "izin": 1,
      "sakit": 2,
      "alpha": 0
    },
    "pct_hadir": 96.7,
    "total_hari": 90
  }
  ```

---

### 5. Pengajuan Izin / Sakit (POST `/api/siswa/izin-sakit`)
Siswa mengajukan permohonan izin/sakit dengan mengunggah foto surat dokter/keterangan dari HP.

* **Headers**: `Authorization: Bearer <token>`, `Content-Type: multipart/form-data`
* **Form-Data**:
  * `status`: `sakit` (atau `izin`)
  * `keterangan`: `Demam tinggi dan berobat`
  * `tanggal`: `2026-08-04` (opsional)
  * `bukti_foto`: `[File Gambar Surat Dokter]` (opsional, mimes: jpeg, png, jpg, max 2MB)
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Pengajuan SAKIT berhasil dikirim.",
    "absensi": {
      "id": 110,
      "status": "sakit",
      "status_label": "Sakit",
      "keterangan": "Demam tinggi (Bukti: storage/izin_sakit/abcd123.jpg)",
      "tanggal": "2026-08-04"
    }
  }
  ```

---

### 6. Jadwal Pelajaran Siswa (GET `/api/siswa/jadwal`)
Melihat jadwal pelajaran mingguan kelas tempat siswa terdaftar.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "hari": "Senin",
        "jam": "07:15 - 08:45",
        "mata_pelajaran": "Pemrograman Web",
        "kode_mapel": "PPLG-WEB",
        "guru": "Budi Santoso, S.Pd.",
        "ruangan": "Lab Komputer 1"
      }
    ]
  }
  ```

---

### 7. Nilai & Evaluasi Harian Siswa (GET `/api/siswa/nilai`)
Melihat daftar nilai tugas, materi, ulangan harian, beserta catatan/feedback guru dan statistik nilai pribadi siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameters (Opsional)**:
  * `search`: Cari nama mapel / bab / judul tugas
  * `jenis`: Filter jenis (`tugas`, `uh`, `uts`, `uas`, `praktikum`)
  * `mapel_id`: Filter ID spesifik mata pelajaran
* **Response 200 OK**:
  ```json
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
        "bab_materi": "Bab 1 - HTML & CSS Base",
        "judul_tugas": "Tugas 1 - Responsive Layout",
        "jenis": "tugas",
        "jenis_label": "Tugas Harian",
        "tanggal": "2026-08-04",
        "tanggal_formatted": "Selasa, 04 Agustus 2026",
        "nilai": 88.5,
        "nilai_formatted": "88.5",
        "kkm": 75,
        "is_tuntas": true,
        "predikat": "B",
        "status": "Tuntas",
        "status_color": "#10b981",
        "catatan_guru": "Sangat baik"
      }
    ]
  }
  ```

---

## 6. 🛠️ MODUL ADMIN (`/api/admin`)
> **Akses Role**: `admin`, `super_admin`

### 1. Executive Dashboard Admin (GET `/api/admin/dashboard`)
Statistik global sekolah, grafik 7 hari, breakdown per kelas, dan status WhatsApp Gateway.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "tanggal": "Selasa, 04 Agustus 2026",
    "ringkasan": {
      "total_siswa": 720,
      "total_guru": 45,
      "total_kelas": 24,
      "total_mapel": 30,
      "hadir": 680,
      "terlambat": 25,
      "izin": 5,
      "sakit": 3,
      "alpha": 7,
      "belum_absen": 0,
      "wa_active": 2,
      "wa_total": 2
    },
    "per_kelas": [
      {
        "id": 1,
        "nama": "X RPL 1",
        "jurusan": "Rekayasa Perangkat Lunak",
        "total": 36,
        "hadir": 34,
        "terlambat": 2,
        "alpha": 0,
        "izin": 1,
        "sakit": 1,
        "belum": 0
      }
    ],
    "chart_7_hari": [
      {
        "tanggal": "2026-08-04",
        "label": "Sel",
        "hadir": 705,
        "alpha": 7,
        "izin_sakit": 8
      }
    ]
  }
  ```

---

### 2. Rekap Absensi Global Sekolah (`GET /api/admin/absensi/rekap`)
Monitoring rekapitulasi kehadiran seluruh siswa sekolah per tanggal.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameters (Opsional)**: `tanggal=YYYY-MM-DD`, `kelas_id=1`, `status=hadir`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "tanggal": "2026-08-04",
    "tanggal_label": "Selasa, 04 Agustus 2026",
    "total": 720,
    "data": [
      {
        "id": 101,
        "siswa_id": 12,
        "nama": "Ahmad Rizky",
        "nis": "20241001",
        "kelas": "X RPL 1",
        "status": "hadir",
        "status_label": "Hadir",
        "status_color": "green",
        "jam_scan": "06:55:10",
        "keterangan": "-"
      }
    ]
  }
  ```

---

### 3. CRUD Data Siswa (`/api/admin/siswa`)

* **List Siswa**: `GET /api/admin/siswa?search=...&kelas_id=...&page=1`
* **Detail Siswa**: `GET /api/admin/siswa/{id}`
* **Tambah Siswa**: `POST /api/admin/siswa`
  * **Body**:
    ```json
    {
      "kelas_id": 1,
      "nis": "20241099",
      "nisn": "0059999999",
      "nama": "Siswa Baru",
      "nama_ortu": "Bapak Siswa",
      "no_wa_ortu": "628123456789"
    }
    ```
* **Update Siswa**: `PUT /api/admin/siswa/{id}`
* **Hapus Siswa**: `DELETE /api/admin/siswa/{id}`

---

### 4. CRUD Data Guru (`/api/admin/guru`)

* **List Guru**: `GET /api/admin/guru?search=...`
* **Tambah Guru**: `POST /api/admin/guru` (Otomatis membuat akun User & assign role `guru`)
  * **Body**:
    ```json
    {
      "nama": "Guru Baru, S.Kom.",
      "nip": "199001012020011002",
      "no_hp": "6281987654321",
      "email": "gurubaru@sekolah.sch.id",
      "password": "password123"
    }
    ```
* **Update Guru**: `PUT /api/admin/guru/{id}`
* **Hapus Guru**: `DELETE /api/admin/guru/{id}`

---

### 5. CRUD Data Kelas & Jurusan (`/api/admin/kelas` & `/api/admin/jurusan`)

* **List Kelas**: `GET /api/admin/kelas`
* **Tambah Kelas**: `POST /api/admin/kelas`
  * **Body**: `{"nama": "XII RPL 2", "jurusan_id": 1, "wali_kelas_id": 2}`
* **Update Kelas**: `PUT /api/admin/kelas/{id}`
* **Hapus Kelas**: `DELETE /api/admin/kelas/{id}`
* **List Jurusan**: `GET /api/admin/jurusan`
* **Tambah Jurusan**: `POST /api/admin/jurusan` (`{"kode": "RPL", "nama": "Rekayasa Perangkat Lunak"}`)

---

### 6. CRUD WA Gateway Sender & Logs (`/api/admin/wa-*`)

* **List WA Sender**: `GET /api/admin/wa-sender`
* **Tambah WA Device**: `POST /api/admin/wa-sender`
  * **Body**: `{"name": "WA Utama", "phone": "628123456789", "status": "aktif"}`
* **Update WA Device**: `PUT /api/admin/wa-sender/{id}`
* **Hapus WA Device**: `DELETE /api/admin/wa-sender/{id}`
* **Log Pengiriman WA**: `GET /api/admin/wa-logs?search=...&page=1`

---

### 7. CRUD User Management (`/api/admin/users`)

* **List User**: `GET /api/admin/users?search=...&role=...&page=1`
* **Tambah User**: `POST /api/admin/users`
  * **Body**: `{"name": "User Admin Baru", "email": "admin2@sekolah.sch.id", "password": "password123", "role": "admin"}`
* **Update User**: `PUT /api/admin/users/{id}`
* **Hapus User**: `DELETE /api/admin/users/{id}`

---

### 8. CRUD Master Mapel (`/api/admin/mapel`)

* **List Mapel**: `GET /api/admin/mapel`
* **Tambah Mapel**: `POST /api/admin/mapel` (`{"kode": "PPLG-01", "nama": "Pemrograman Web", "kelompok": "produktif"}`)
* **Update Mapel**: `PUT /api/admin/mapel/{id}`
* **Hapus Mapel**: `DELETE /api/admin/mapel/{id}`

---

### 9. CRUD Master Jadwal Pelajaran (`/api/admin/jadwal`)

* **List Jadwal**: `GET /api/admin/jadwal?kelas_id=...&hari=...`
* **Tambah Jadwal**: `POST /api/admin/jadwal`
  * **Body**: `{"kelas_id": 1, "mata_pelajaran_id": 3, "guru_id": 2, "hari": "senin", "jam_mulai": "07:15", "jam_selesai": "08:45", "ruangan": "Lab 1"}`
* **Update Jadwal**: `PUT /api/admin/jadwal/{id}`
* **Hapus Jadwal**: `DELETE /api/admin/jadwal/{id}`

---

### 10. Pengaturan Jam Absensi Sekolah (`/api/admin/pengaturan-absensi`)

* **Get Pengaturan**: `GET /api/admin/pengaturan-absensi`
* **Update Pengaturan**: `POST /api/admin/pengaturan-absensi`
  * **Body**:
    ```json
    {
      "jam_masuk_batas": "07:00:00",
      "jam_absensi_tutup": "12:00:00",
      "aktif_sabtu": false
    }
    ```


---

## 7. ⚠️ FORMAT ERROR & STATUS CODE

Aplikasi Android disarankan menangani kode HTTP status standar berikut:

* **200 OK**: Request berhasil.
* **201 Created**: Data berhasil dibuat.
* **401 Unauthorized**: Bearer Token kadaluarsa, salah, atau tidak disertakan.
  ```json
  {
    "message": "Unauthenticated."
  }
  ```
* **403 Forbidden**: Akun tidak memiliki hak akses role (misal siswa mencoba akses rute guru/admin).
* **404 Not Found**: Data atau endpoint tidak ditemukan.
* **422 Unprocessable Entity**: Validation Error dari input user.
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "email": ["Email wajib diisi."],
      "password": ["Password minimal 6 karakter."]
    }
  }
  ```
* **500 Internal Server Error**: Fatal exception server backend.

---

## 8. 💻 CONTOH CODE CLIENT ANDROID (KOTLIN + RETROFIT)

Berikut contoh implementasi Retrofit Client pada aplikasi Android Native (Kotlin):

### 1. Interface `ApiService.kt`
```kotlin
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // Auth
    @POST("api/auth/login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    @GET("api/auth/me")
    suspend fun getProfile(
        @Header("Authorization") token: String
    ): Response<ProfileResponse>

    // Guru Scan QR
    @POST("api/guru/absensi/scan")
    suspend fun scanQr(
        @Header("Authorization") token: String,
        @Body request: ScanQrRequest
    ): Response<ScanQrResponse>

    // Siswa Display Profile
    @GET("api/siswa/profile")
    suspend fun getSiswaProfile(
        @Header("Authorization") token: String
    ): Response<SiswaProfileResponse>
}
```

### 2. Request Data Models
```kotlin
data class LoginRequest(
    val email: String,
    val password: String,
    val device_name: String = "Android Mobile App"
)

data class ScanQrRequest(
    val qr_token: String,
    val kelas_id: Int
)
```

---

*Dokumentasi API Android Sistem Absensi MHC — Developed by **qpawdeveloper**.*
