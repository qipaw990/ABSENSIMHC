# 📱 DOKUMENTASI RESTFUL API APLIKASI MOBILE ANDROID
> **Developer:** qpawdeveloper  
> **Auth Method:** Laravel Sanctum (Bearer Token)  
> **Format Data:** JSON (`Content-Type: application/json` & `Accept: application/json`)

Dokumen ini berisi spesifikasi lengkap **RESTful API** untuk pengembangan aplikasi Android (Native Kotlin/Java, Flutter, maupun React Native) pada **Sistem Absensi QR Code & WA**.

---

## 📑 DAFTAR ISI
1. [Standar Request & Headers](#1-standar-request--headers)
2. [Modul Autentikasi (`/api/auth`)](#2-modul-autentikasi-apiauth)
3. [Modul Guru / Wali Kelas (`/api/guru`)](#3-modul-guru--wali-kelas-apiguru)
4. [Modul Siswa (`/api/siswa`)](#4-modul-siswa-apisiswa)
5. [Modul Admin (`/api/admin`)](#5-modul-admin-apiadmin)
6. [Format Response & Penanganan Error](#6-format-response--penanganan-error)
7. [Contoh Implementasi Client Android (Kotlin + Retrofit)](#7-contoh-implementasi-client-android-kotlin--retrofit)

---

## 1. 🌐 STANDAR REQUEST & HEADERS

* **Base URL**: `https://absensi.sekolah.sch.id` (atau `http://IP_SERVER:8585`)
* **Headers Publik (Login)**:
  ```http
  Content-Type: application/json
  Accept: application/json
  ```
* **Headers Protected (Butuh Autentikasi Token)**:
  ```http
  Authorization: Bearer <TOKEN_SANCTUM>
  Content-Type: application/json
  Accept: application/json
  ```

---

## 2. 🔑 MODUL AUTENTIKASI (`/api/auth`)

### 1. Login User (POST `/api/auth/login`)
Digunakan oleh aplikasi Android untuk melakukan autentikasi login (Guru, Siswa, Admin).

* **Request Body**:
  ```json
  {
    "email": "guru@sekolah.com",
    "password": "Password123!",
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
      "email": "guru@sekolah.com",
      "role": "guru",
      "roles": ["guru"],
      "guru": {
        "id": 2,
        "nip": "198501012010011001",
        "nama": "Budi Santoso, S.Pd.",
        "foto": "https://domain.com/storage/guru/budi.jpg"
      }
    }
  }
  ```

---

### 2. Profile User saat ini (GET `/api/auth/me`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "user": {
      "id": 5,
      "name": "Budi Santoso",
      "email": "guru@sekolah.com",
      "role": "guru"
    }
  }
  ```

---

### 3. Ubah Password (POST `/api/auth/change-password`)
* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "current_password": "PasswordLama123",
    "new_password": "PasswordBaru456",
    "new_password_confirmation": "PasswordBaru456"
  }
  ```
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Password berhasil diperbarui."
  }
  ```

---

### 4. Logout User (POST `/api/auth/logout`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Berhasil logout."
  }
  ```

---

## 3. 👨‍🏫 MODUL GURU / WALI KELAS (`/api/guru`)

### 1. Daftar Kelas yang Diampu (GET `/api/guru/kelas`)
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

### 2. Scan QR Code Absensi Siswa (POST `/api/guru/absensi/scan`)
Fungsi utama kamera scanner Android milik Guru untuk memproses QR Code Siswa.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "qr_token": "token_qr_32_karakter_siswa",
    "kelas_id": 1
  }
  ```
* **Response 200 OK (Berhasil Scan)**:
  ```json
  {
    "success": true,
    "message": "Absensi BERHASIL! Ahmad Rizky tercatat HADIR (Jam 06:55:10). Notifikasi WA terkirim.",
    "siswa": {
      "nama": "Ahmad Rizky",
      "nis": "20241001",
      "kelas": "X RPL 1",
      "foto_url": "https://.../siswa.jpg"
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

### 3. Presensi Manual Siswa (POST `/api/guru/absensi/manual`)
Digunakan jika siswa tidak membawa HP/Kartu Pelajar QR Code.

* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "siswa_id": 12,
    "kelas_id": 1,
    "status": "hadir",
    "keterangan": "Kartu QR tertinggal",
    "tanggal": "2026-08-02"
  }
  ```
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
      "jam_scan": "07:10:00",
      "tanggal": "2026-08-02"
    }
  }
  ```

---

### 4. Rekap Absensi Kelas per Tanggal (GET `/api/guru/absensi/rekap/{kelas_id}?tanggal=YYYY-MM-DD`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
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
  ```

---

### 5. Edit Data Absensi (PUT `/api/guru/absensi/{id}`)
* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "status": "terlambat",
    "jam_scan": "07:25:00",
    "keterangan": "Terlambat 15 menit"
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
      "keterangan": "Terlambat 15 menit"
    }
  }
  ```

---

### 6. Hapus Data Absensi (DELETE `/api/guru/absensi/{id}`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Data absensi berhasil dihapus."
  }
  ```

---

### 7. Jadwal Mengajar Guru (GET `/api/guru/jadwal`)
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

### 8. Daftar Tugas & Penilaian Guru (GET `/api/guru/penilaian?kelas_id=1`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 10,
        "kelas": "X RPL 1",
        "mata_pelajaran": "Pemrograman Web",
        "bab_materi": "Bab 1 - Dasar HTML & CSS",
        "judul_tugas": "Tugas 1 - Layout Landing Page",
        "jenis_label": "Tugas Harian",
        "tanggal": "2026-08-03",
        "total_siswa": 32,
        "sudah_dinilai": 30
      }
    ]
  }
  ```

---

### 9. Detail & Entri Nilai Siswa (GET `/api/guru/penilaian/{id}`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "penilaian": {
      "id": 10,
      "kelas": "X RPL 1",
      "mata_pelajaran": "Pemrograman Web",
      "bab_materi": "Bab 1 - Dasar HTML & CSS",
      "judul_tugas": "Tugas 1 - Layout Landing Page"
    },
    "nilai_siswa": [
      {
        "id": 101,
        "siswa_id": 12,
        "nama_siswa": "Ahmad Rizky",
        "nis": "20241001",
        "nilai": 88.5,
        "catatan_guru": "Bagus, layout sangat rapi",
        "status": "Tuntas"
      }
    ]
  }
  ```

---

### 10. Simpan Batch Nilai Siswa via Mobile (POST `/api/guru/penilaian/{id}/nilai-batch`)
* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
  ```json
  {
    "nilai": {
      "101": 88.5,
      "102": 75.0
    },
    "catatan_guru": {
      "101": "Sangat rapi",
      "102": "Perlu diperbaiki CSS flexbox"
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

## 4. 🎓 MODUL SISWA (`/api/siswa`)

### 1. Profil & Display QR Code Siswa (GET `/api/siswa/profile`)
Fungsi layar utama HP Siswa untuk menampilkan QR Code diri yang siap di-scan guru.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "siswa": {
      "id": 12,
      "nis": "20241001",
      "nama": "Ahmad Rizky",
      "foto_url": "https://.../photo.jpg",
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
  ```

---

### 2. Riwayat Absensi Siswa (GET `/api/siswa/absensi?bulan=2026-08`)
* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
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
  ```

---

### 3. Pengajuan Izin / Sakit oleh Siswa (POST `/api/siswa/izin-sakit`)
Siswa mengajukan surat izin/sakit dengan mengunggah foto surat dari HP.

* **Headers**: `Authorization: Bearer <token>`, `Content-Type: multipart/form-data`
* **Form Data**:
  * `status`: `sakit` (atau `izin`)
  * `keterangan`: `Demam tinggi dan berobat ke dokter`
  * `bukti_foto`: `[File Gambar Surat Dokter]` (Optional)
* **Response 200 OK**:
  ```json
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
  ```

---

### 4. Jadwal Pelajaran Siswa (GET `/api/siswa/jadwal`)
Siswa melihat jadwal pelajaran mingguan kelasnya.

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
        "kode_mapel": "MP-PWPB-X",
        "guru": "Budi Santoso, S.Pd.",
        "ruangan": "Lab Komputer 1"
      }
    ]
  }
  ```

---

### 5. Nilai & Evaluasi Harian Siswa (GET `/api/siswa/nilai`)
Siswa melihat daftar nilai tugas, bab materi, ulangan, beserta rincian catatan/feedback dari guru.

* **Headers**: `Authorization: Bearer <token>`
* **Response 200 OK**:
  ```json
  {
    "success": true,
    "rata_rata": 86.5,
    "data": [
      {
        "id": 101,
        "mata_pelajaran": "Pemrograman Web",
        "bab_materi": "Bab 1 - Dasar HTML & CSS",
        "judul_tugas": "Tugas 1 - Layout Landing Page",
        "jenis_label": "Tugas Harian",
        "tanggal": "2026-08-03",
        "nilai": 88.5,
        "catatan_guru": "Sangat rapi",
        "status": "Tuntas"
      }
    ]
  }
  ```

---

## 5. 🛠️ MODUL ADMIN (`/api/admin`)

### 1. Dashboard Admin (GET `/api/admin/dashboard`)
* **Response 200 OK**:
  ```json
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
    }
  }
  ```

---

### 2. Status Device WA Sender (GET `/api/admin/wa-sender`)
* **Response 200 OK**:
  ```json
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
  ```

---

## 6. ⚠️ FORMAT RESPONSE & ERROR HANDLING

* **Error 401 (Unauthorized / Token Expired)**:
  ```json
  {
    "message": "Unauthenticated."
  }
  ```
* **Error 422 (Validation Error)**:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "email": ["Email wajib diisi."],
      "password": ["Password minimal 6 karakter."]
    }
  }
  ```

---

## 7. 💻 CONTOH IMPLEMENTASI CLIENT ANDROID (KOTLIN + RETROFIT)

Berikut adalah draf contoh implementasi API Client di Kotlin untuk aplikasi Android:

```kotlin
// ApiService.kt
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    @POST("api/auth/login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    @GET("api/guru/kelas")
    suspend fun getKelasGuru(
        @Header("Authorization") token: String
    ): Response<KelasListResponse>

    @POST("api/guru/absensi/scan")
    suspend fun scanQr(
        @Header("Authorization") token: String,
        @Body request: ScanQrRequest
    ): Response<ScanQrResponse>
}

// Data Models
data class LoginRequest(
    val email: String,
    val password: String,
    val device_name: String = "Android Phone"
)

data class ScanQrRequest(
    val qr_token: String,
    val kelas_id: Int
)

data class ScanQrResponse(
    val success: Boolean,
    val message: String,
    val siswa: SiswaData?,
    val absensi: AbsensiData?
)
```

---

*Dokumentasi API Android — Developed by **qpawdeveloper**.*
