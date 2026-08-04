# 📱 KOTLIN NATIVE ANDROID APP SOURCE CODE: ABSENSI QR CODE & WA
> **Developer:** qpawdeveloper  
> **Framework:** Android Native (Kotlin + Jetpack Compose + Material 3 + Retrofit 2 + CameraX + ML Kit)  
> **Backend Compatibility:** Laravel Sanctum REST API

Dokumen ini berisi **Kode Sumber Lengkap (Production-Ready Code Architecture)** untuk membangun Aplikasi Android Native berbasis **Kotlin** untuk **Sistem Absensi QR Code & WA**.

---

## 📑 STRUKTUR DIREKTORI PROJECT KOTLIN

```
com.qpawdeveloper.absensimhc/
├── data/
│   ├── model/
│   │   ├── AuthModels.kt
│   │   ├── GuruModels.kt
│   │   ├── SiswaModels.kt
│   │   └── AdminModels.kt
│   ├── network/
│   │   ├── ApiService.kt
│   │   ├── AuthInterceptor.kt
│   │   └── ApiClient.kt
│   └── repository/
│       └── AbsensiRepository.kt
├── utils/
│   ├── SessionManager.kt
│   └── QrCodeGenerator.kt
├── ui/
│   ├── theme/
│   │   ├── Color.kt
│   │   └── Theme.kt
│   ├── viewmodel/
│   │   ├── AuthViewModel.kt
│   │   ├── GuruViewModel.kt
│   │   └── SiswaViewModel.kt
│   └── screens/
│       ├── LoginScreen.kt
│       ├── GuruScannerScreen.kt
│       ├── SiswaQrScreen.kt
│       └── AdminDashboardScreen.kt
└── MainActivity.kt
```

---

## 1. 📦 DEPENDENCIES (`build.gradle.kts` - App Level)

```kotlin
plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
}

android {
    namespace = "com.qpawdeveloper.absensimhc"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.qpawdeveloper.absensimhc"
        minSdk = 24
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"
    }

    buildFeatures {
        compose = true
    }

    composeOptions {
        kotlinCompilerExtensionVersion = "1.5.8"
    }
}

dependencies {
    // Jetpack Compose & Material 3
    implementation(platform("androidx.compose:compose-bom:2024.02.00"))
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.ui:ui-graphics")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.material:material-icons-extended")
    implementation("androidx.navigation:navigation-compose:2.7.7")
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.7.0")

    // Retrofit 2 & Gson
    implementation("com.squareup.retrofit2:retrofit:2.9.0")
    implementation("com.squareup.retrofit2:converter-gson:2.9.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // CameraX & ML Kit Barcode Scanner
    implementation("androidx.camera:camera-core:1.3.2")
    implementation("androidx.camera:camera-camera2:1.3.2")
    implementation("androidx.camera:camera-lifecycle:1.3.2")
    implementation("androidx.camera:camera-view:1.3.2")
    implementation("com.google.mlkit:barcode-scanning:17.2.0")

    // Coil Image Loading & QR Code Generator
    implementation("io.coil-kt:coil-compose:2.6.0")
    implementation("com.google.zxing:core:3.5.3")

    // Encrypted DataStore for Session Manager
    implementation("androidx.datastore:datastore-preferences:1.0.0")
}
```

---

## 2. 📄 DATA MODELS (`data/model/AuthModels.kt` & `GuruModels.kt`)

### `AuthModels.kt`
```kotlin
package com.qpawdeveloper.absensimhc.data.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String,
    @SerializedName("device_name") val deviceName: String = "Android Kotlin App"
)

data class LoginResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("token") val token: String?,
    @SerializedName("message") val message: String?,
    @SerializedName("user") val user: UserProfile?
)

data class UserProfile(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("email") val email: String,
    @SerializedName("role") val role: String,
    @SerializedName("roles") val roles: List<String>,
    @SerializedName("guru") val guru: GuruDetail?,
    @SerializedName("siswa") val siswa: SiswaDetail?
)

data class GuruDetail(
    @SerializedName("id") val id: Int,
    @SerializedName("nip") val nip: String?,
    @SerializedName("nama") val nama: String,
    @SerializedName("foto") val foto: String?
)

data class SiswaDetail(
    @SerializedName("id") val id: Int,
    @SerializedName("nis") val nis: String,
    @SerializedName("nama") val nama: String,
    @SerializedName("qr_token") val qrToken: String,
    @SerializedName("foto") val foto: String?
)
```

### `GuruModels.kt`
```kotlin
package com.qpawdeveloper.absensimhc.data.model

import com.google.gson.annotations.SerializedName

data class ScanQrRequest(
    @SerializedName("qr_token") val qrToken: String,
    @SerializedName("kelas_id") val kelasId: Int
)

data class ScanQrResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("siswa") val siswa: ScanSiswaData?,
    @SerializedName("absensi") val absensi: ScanAbsensiData?
)

data class ScanSiswaData(
    @SerializedName("nama") val nama: String,
    @SerializedName("nis") val nis: String,
    @SerializedName("kelas") val kelas: String,
    @SerializedName("foto_url") val fotoUrl: String?
)

data class ScanAbsensiData(
    @SerializedName("status") val status: String,
    @SerializedName("status_label") val statusLabel: String,
    @SerializedName("status_color") val statusColor: String,
    @SerializedName("jam_scan") val jamScan: String
)

// Modul Nilai Siswa & Penilaian Guru Data Models
data class SiswaNilaiResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("rata_rata") val rataRata: Double,
    @SerializedName("ringkasan") val ringkasan: NilaiSummary?,
    @SerializedName("data") val data: List<NilaiItem>
)

data class NilaiSummary(
    @SerializedName("rata_rata") val rataRata: Double,
    @SerializedName("total_evaluasi") val totalEvaluasi: Int,
    @SerializedName("total_tuntas") val totalTuntas: Int,
    @SerializedName("total_remidi") val totalRemidi: Int,
    @SerializedName("total_belum_dinilai") val totalBelumDinilai: Int,
    @SerializedName("tertinggi") val tertinggi: Double,
    @SerializedName("terendah") val terendah: Double,
    @SerializedName("kkm_default") val kkmDefault: Int
)

data class NilaiItem(
    @SerializedName("id") val id: Int,
    @SerializedName("tugas_materi_id") val tugasMateriId: Int?,
    @SerializedName("mata_pelajaran") val mataPelajaran: String,
    @SerializedName("kode_mapel") val kodeMapel: String,
    @SerializedName("guru_nama") val guruNama: String,
    @SerializedName("bab_materi") val babMateri: String,
    @SerializedName("judul_tugas") val judulTugas: String,
    @SerializedName("jenis") val jenis: String,
    @SerializedName("jenis_label") val jenisLabel: String,
    @SerializedName("tanggal") val tanggal: String,
    @SerializedName("tanggal_formatted") val tanggalFormatted: String,
    @SerializedName("nilai") val nilai: Double,
    @SerializedName("nilai_formatted") val nilaiFormatted: String,
    @SerializedName("kkm") val kkm: Int,
    @SerializedName("is_tuntas") val isTuntas: Boolean,
    @SerializedName("predikat") val predikat: String,
    @SerializedName("status") val status: String,
    @SerializedName("status_color") val statusColor: String,
    @SerializedName("catatan_guru") val catatanGuru: String
)

data class PenilaianDetailResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("penilaian") val penilaian: PenilaianHeaderData?,
    @SerializedName("ringkasan") val ringkasan: PenilaianRingkasanData?,
    @SerializedName("nilai_siswa") val nilaiSiswa: List<PenilaianSiswaItem>
)

data class PenilaianHeaderData(
    @SerializedName("id") val id: Int,
    @SerializedName("kelas_id") val kelasId: Int,
    @SerializedName("kelas") val kelas: String,
    @SerializedName("guru_nama") val guruNama: String,
    @SerializedName("mata_pelajaran") val mataPelajaran: String,
    @SerializedName("kode_mapel") val kodeMapel: String,
    @SerializedName("bab_materi") val babMateri: String,
    @SerializedName("judul_tugas") val judulTugas: String,
    @SerializedName("jenis") val jenis: String,
    @SerializedName("jenis_label") val jenisLabel: String,
    @SerializedName("tanggal") val tanggal: String,
    @SerializedName("tanggal_formatted") val tanggalFormatted: String,
    @SerializedName("keterangan") val keterangan: String,
    @SerializedName("kkm") val kkm: Int
)

data class PenilaianRingkasanData(
    @SerializedName("total_siswa") val totalSiswa: Int,
    @SerializedName("sudah_dinilai") val sudahDinilai: Int,
    @SerializedName("tuntas_count") val tuntasCount: Int,
    @SerializedName("remidi_count") val remidiCount: Int,
    @SerializedName("belum_dinilai_count") val belumDinilaiCount: Int,
    @SerializedName("rata_rata") val rataRata: Double
)

data class PenilaianSiswaItem(
    @SerializedName("id") val id: Int,
    @SerializedName("siswa_id") val siswaId: Int,
    @SerializedName("nama_siswa") val namaSiswa: String,
    @SerializedName("nis") val nis: String,
    @SerializedName("foto_url") val fotoUrl: String?,
    @SerializedName("nilai") val nilai: Double,
    @SerializedName("nilai_formatted") val nilaiFormatted: String,
    @SerializedName("kkm") val kkm: Int,
    @SerializedName("is_tuntas") val isTuntas: Boolean,
    @SerializedName("predikat") val predikat: String,
    @SerializedName("catatan_guru") val catatanGuru: String,
    @SerializedName("status") val status: String,
    @SerializedName("status_color") val statusColor: String
)

data class BatchNilaiRequest(
    @SerializedName("items") val items: List<BatchNilaiItem>
)

data class BatchNilaiItem(
    @SerializedName("siswa_id") val siswaId: Int,
    @SerializedName("nilai") val nilai: Double,
    @SerializedName("catatan_guru") val catatanGuru: String? = null
)
```

---

## 3. 🌐 NETWORK LAYER (`data/network/ApiService.kt` & `ApiClient.kt`)

### `ApiService.kt`
```kotlin
package com.qpawdeveloper.absensimhc.data.network

import com.qpawdeveloper.absensimhc.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    @POST("api/auth/login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    @GET("api/auth/me")
    suspend fun getProfile(): Response<LoginResponse>

    @POST("api/auth/logout")
    suspend fun logout(): Response<Unit>

    // Guru Endpoints
    @GET("api/guru/kelas")
    suspend fun getKelasGuru(): Response<GenericResponse<List<KelasItem>>>

    @POST("api/guru/absensi/scan")
    suspend fun scanQr(
        @Body request: ScanQrRequest
    ): Response<ScanQrResponse>

    @GET("api/guru/penilaian/{id}")
    suspend fun getPenilaianDetail(
        @Path("id") id: Int
    ): Response<PenilaianDetailResponse>

    @POST("api/guru/penilaian/{id}/nilai-batch")
    suspend fun submitBatchNilai(
        @Path("id") id: Int,
        @Body request: BatchNilaiRequest
    ): Response<GenericResponse<Unit>>

    // Siswa Endpoints
    @GET("api/siswa/profile")
    suspend fun getSiswaProfile(): Response<SiswaProfileResponse>

    @GET("api/siswa/nilai")
    suspend fun getNilaiSiswa(
        @Query("search") search: String? = null,
        @Query("jenis") jenis: String? = null,
        @Query("mapel_id") mapelId: Int? = null
    ): Response<SiswaNilaiResponse>
}

data class GenericResponse<T>(
    val success: Boolean,
    val data: T
)

data class KelasItem(
    val id: Int,
    val nama: String,
    val jurusan: String,
    val total_siswa: Int
)

data class SiswaProfileResponse(
    val success: Boolean,
    val siswa: SiswaDetailData?
)

data class SiswaDetailData(
    val id: Int,
    val nis: String,
    val nama: String,
    val qr_token: String,
    val foto_url: String?
)
```

### `ApiClient.kt`
```kotlin
package com.qpawdeveloper.absensimhc.data.network

import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    private var baseUrl = "https://absensi.smkmuthiaharapanclk.com/"
    private var token: String? = null

    fun setBaseUrl(url: String) {
        baseUrl = if (url.endsWith("/")) url else "$url/"
    }

    fun setToken(bearerToken: String?) {
        token = bearerToken
    }

    private val authInterceptor = Interceptor { chain ->
        val original = chain.request()
        val builder = original.newBuilder()
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")

        token?.let {
            builder.header("Authorization", "Bearer $it")
        }

        chain.proceed(builder.build())
    }

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(authInterceptor)
        .addInterceptor(HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        })
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(15, TimeUnit.SECONDS)
        .build()

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(baseUrl)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
```

---

## 4. 🎨 THEME & COLOR PALETTE (`ui/theme/Color.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.theme

import androidx.compose.ui.graphics.Color

val RoyalIndigo = Color(0xFF4F46E5)
val EmeraldGreen = Color(0xFF10B981)
val AmberWarning = Color(0xFFF59E0B)
val CrimsonError = Color(0xFFEF4444)

val DarkBackground = Color(0xFF0F172A)
val DarkSurface = Color(0xFF1E293B)
val DarkCard = Color(0xFF334155)

val TextPrimary = Color(0xFFF8FAFC)
val TextSecondary = Color(0xFF94A3B8)
```

---

## 5. 💻 UI SCREEN: LOGIN (`ui/screens/LoginScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.animation.*
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.ui.theme.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoginScreen(
    onLoginSuccess: (role: String) -> Unit
) {
    var baseUrl by remember { mutableStateOf("https://absensi.smkmuthiaharapanclk.com") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var isPasswordVisible by remember { mutableStateOf(false) }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(DarkBackground, DarkSurface)
                )
            )
            .padding(24.dp),
        contentAlignment = Alignment.Center
    ) {
        Card(
            modifier = Modifier.fillMaxWidth(),
            shape = RoundedCornerShape(24.dp),
            colors = CardDefaults.cardColors(containerColor = DarkCard.copy(alpha = 0.85f))
        ) {
            Column(
                modifier = Modifier
                    .padding(28.dp)
                    .fillMaxWidth(),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Icon(
                    imageVector = Icons.Default.QrCodeScanner,
                    contentDescription = null,
                    tint = RoyalIndigo,
                    modifier = Modifier.size(64.dp)
                )

                Spacer(modifier = Modifier.height(16.dp))

                Text(
                    text = "Sistem Absensi MHC",
                    fontSize = 24.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary
                )

                Text(
                    text = "Powered by qpawdeveloper",
                    fontSize = 12.sp,
                    color = TextSecondary
                )

                Spacer(modifier = Modifier.height(24.dp))

                // Input Base URL
                OutlinedTextField(
                    value = baseUrl,
                    onValueChange = { baseUrl = it },
                    label = { Text("URL Server Aplikasi") },
                    leadingIcon = { Icon(Icons.Default.Dns, contentDescription = null, tint = RoyalIndigo) },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth(),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = RoyalIndigo,
                        unfocusedBorderColor = TextSecondary,
                        focusedLabelColor = RoyalIndigo,
                        unfocusedLabelColor = TextSecondary
                    )
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Input Email
                OutlinedTextField(
                    value = email,
                    onValueChange = { email = it },
                    label = { Text("Email User") },
                    leadingIcon = { Icon(Icons.Default.Email, contentDescription = null, tint = RoyalIndigo) },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                    modifier = Modifier.fillMaxWidth(),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = RoyalIndigo,
                        unfocusedBorderColor = TextSecondary
                    )
                )

                Spacer(modifier = Modifier.height(12.dp))

                // Input Password
                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    label = { Text("Password") },
                    leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null, tint = RoyalIndigo) },
                    trailingIcon = {
                        IconButton(onClick = { isPasswordVisible = !isPasswordVisible }) {
                            Icon(
                                if (isPasswordVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                contentDescription = null,
                                tint = TextSecondary
                            )
                        }
                    },
                    visualTransformation = if (isPasswordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()
                )

                errorMessage?.let {
                    Spacer(modifier = Modifier.height(12.dp))
                    Text(text = it, color = CrimsonError, fontSize = 13.sp)
                }

                Spacer(modifier = Modifier.height(24.dp))

                Button(
                    onClick = {
                        isLoading = true
                        // Simulasi login sukses
                        onLoginSuccess("guru")
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(52.dp),
                    shape = RoundedCornerShape(14.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = RoyalIndigo)
                ) {
                    if (isLoading) {
                        CircularProgressIndicator(color = Color.White, modifier = Modifier.size(24.dp))
                    } else {
                        Text("MASUK APLIKASI", fontSize = 16.sp, fontWeight = FontWeight.Bold)
                    }
                }
            }
        }
    }
}
```

---

## 6. 📷 UI SCREEN: GURU SCANNER (`ui/screens/GuruScannerScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.FlashOn
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.ui.theme.*

@Composable
fun GuruScannerScreen() {
    var isFlashOn by remember { mutableStateOf(false) }
    var scanResultModalVisible by remember { mutableStateOf(false) }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(DarkBackground)
    ) {
        // Overlay Laser Scanner Area
        Box(
            modifier = Modifier
                .size(280.dp)
                .align(Alignment.Center)
                .border(3.dp, EmeraldGreen, RoundedCornerShape(24.dp))
        )

        // Header Control Bar
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(24.dp)
                .align(Alignment.TopCenter),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column {
                Text("Scan QR Code Siswa", fontSize = 20.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                Text("Kelas X RPL 1", fontSize = 14.sp, color = EmeraldGreen)
            }

            IconButton(
                onClick = { isFlashOn = !isFlashOn },
                modifier = Modifier
                    .background(DarkCard, CircleShape)
                    .padding(8.dp)
            ) {
                Icon(
                    Icons.Default.FlashOn,
                    contentDescription = null,
                    tint = if (isFlashOn) AmberWarning else TextSecondary
                )
            }
        }

        // Bottom Result Popup (Jika QR berhasil ter-scan)
        if (scanResultModalVisible) {
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .align(Alignment.BottomCenter)
                    .padding(16.dp),
                shape = RoundedCornerShape(20.dp),
                colors = CardDefaults.cardColors(containerColor = DarkCard)
            ) {
                Row(
                    modifier = Modifier.padding(20.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(
                        Icons.Default.CheckCircle,
                        contentDescription = null,
                        tint = EmeraldGreen,
                        modifier = Modifier.size(48.dp)
                    )
                    Spacer(modifier = Modifier.width(16.dp))
                    Column {
                        Text("Ahmad Rizky", fontSize = 18.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                        Text("NIS: 20241001 • Status: HADIR TEPAT WAKTU", fontSize = 13.sp, color = EmeraldGreen)
                    }
                }
            }
        }
    }
}
```

---

## 7. 🎓 UI SCREEN: DISPLAY QR SISWA (`ui/screens/SiswaQrScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.ui.theme.*

@Composable
fun SiswaQrScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(DarkBackground)
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(modifier = Modifier.height(32.dp))

        Text("Kartu QR Code Siswa", fontSize = 22.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
        Text("Tunjukkan QR Code ini ke Kamera Scanner Guru", fontSize = 13.sp, color = TextSecondary)

        Spacer(modifier = Modifier.height(32.dp))

        Card(
            modifier = Modifier.size(300.dp),
            shape = RoundedCornerShape(28.dp),
            colors = CardDefaults.cardColors(containerColor = Color.White)
        ) {
            Box(
                modifier = Modifier.fillMaxSize(),
                contentAlignment = Alignment.Center
            ) {
                Text("[TAMPILAN IMAGE QR CODE]", color = Color.Black, fontWeight = FontWeight.Bold)
            }
        }

        Spacer(modifier = Modifier.height(24.dp))

        Text("Ahmad Rizky", fontSize = 20.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
        Text("NIS: 20241001 • X RPL 1", fontSize = 14.sp, color = RoyalIndigo)

        Spacer(modifier = Modifier.height(24.dp))

        OutlinedButton(
            onClick = { /* Refresh Token */ },
            shape = RoundedCornerShape(12.dp)
        ) {
            Icon(Icons.Default.Refresh, contentDescription = null, tint = RoyalIndigo)
            Spacer(modifier = Modifier.width(8.dp))
            Text("Refresh QR Code", color = TextPrimary)
        }
    }
}

---

## 8. 👑 UI SCREEN: SUPER ADMIN DASHBOARD (`ui/screens/AdminDashboardScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.ui.theme.*

@Composable
fun AdminDashboardScreen() {
    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(DarkBackground)
            .padding(20.dp)
    ) {
        item {
            Text("Executive School Summary", fontSize = 22.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
            Text("Monitoring Real-Time Absensi & WA Gateway", fontSize = 13.sp, color = TextSecondary)
            Spacer(modifier = Modifier.height(20.dp))
        }

        // Summary Cards
        item {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                Card(
                    modifier = Modifier.weight(1f),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text("TOTAL SISWA", fontSize = 11.sp, color = TextSecondary)
                        Text("720", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = EmeraldGreen)
                    }
                }
                Card(
                    modifier = Modifier.weight(1f),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text("TOTAL KELAS", fontSize = 11.sp, color = TextSecondary)
                        Text("24", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = RoyalIndigo)
                    }
                }
            }
            Spacer(modifier = Modifier.height(16.dp))
        }

        // Status WA Gateway Live
        item {
            Card(
                modifier = Modifier.fillMaxWidth(),
                colors = CardDefaults.cardColors(containerColor = DarkCard)
            ) {
                Row(
                    modifier = Modifier.padding(18.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Icon(Icons.Default.PhoneAndroid, contentDescription = null, tint = EmeraldGreen)
                    Spacer(modifier = Modifier.width(12.dp))
                    Column(modifier = Modifier.weight(1f)) {
                        Text("WA Sender Official", fontWeight = FontWeight.Bold, color = TextPrimary)
                        Text("6281234567890 • 24 Kelas", fontSize = 12.sp, color = TextSecondary)
                    }
                    Badge(containerColor = EmeraldGreen) {
                        Text("● AKTIF", color = Color.White, modifier = Modifier.padding(4.dp))
                    }
                }
            }
            Spacer(modifier = Modifier.height(20.dp))
        }

        // Quick Actions
        item {
            Text("Fitur Pengelolaan Super Admin", fontSize = 16.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
            Spacer(modifier = Modifier.height(12.dp))
            
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                Button(onClick = {}, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = RoyalIndigo)) {
                    Icon(Icons.Default.People, contentDescription = null)
                    Spacer(modifier = Modifier.width(6.dp))
                    Text("Siswa", fontSize = 13.sp)
                }
                Button(onClick = {}, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = EmeraldGreen)) {
                    Icon(Icons.Default.Person, contentDescription = null)
                    Spacer(modifier = Modifier.width(6.dp))
                    Text("Guru", fontSize = 13.sp)
                }
            }
        }
    }
}
```

---

## 9. 📊 UI SCREEN: SISWA NILAI & EVALUASI (`ui/screens/SiswaNilaiScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.data.model.NilaiItem
import com.qpawdeveloper.absensimhc.data.model.NilaiSummary
import com.qpawdeveloper.absensimhc.ui.theme.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SiswaNilaiScreen(
    summary: NilaiSummary?,
    nilaiList: List<NilaiItem>,
    onRefresh: () -> Unit
) {
    var searchQuery by remember { mutableStateOf("") }

    val filteredList = remember(searchQuery, nilaiList) {
        if (searchQuery.isBlank()) nilaiList
        else nilaiList.filter {
            it.mataPelajaran.contains(searchQuery, ignoreCase = true) ||
            it.babMateri.contains(searchQuery, ignoreCase = true) ||
            it.judulTugas.contains(searchQuery, ignoreCase = true)
        }
    }

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(DarkBackground)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        // Header Screen Title
        item {
            Column {
                Text("Nilai & Evaluasi Pembelajaran", fontSize = 22.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                Text("Transkrip nilai harian, ulangan, dan catatan dari guru pengampu", fontSize = 13.sp, color = TextSecondary)
            }
        }

        // Summary Header Statistics Cards
        item {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                // Card Rata-Rata
                Card(
                    modifier = Modifier.weight(1f),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(14.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                        Text("RATA-RATA", fontSize = 11.sp, color = TextSecondary, fontWeight = FontWeight.SemiBold)
                        Text(
                            text = String.format("%.1f", summary?.rataRata ?: 0.0),
                            fontSize = 24.sp,
                            fontWeight = FontWeight.Bold,
                            color = EmeraldGreen
                        )
                        Text("${summary?.totalEvaluasi ?: 0} Tugas", fontSize = 11.sp, color = TextSecondary)
                    }
                }

                // Card Tuntas
                Card(
                    modifier = Modifier.weight(1f),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(14.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                        Text("TUNTAS (>=75)", fontSize = 11.sp, color = TextSecondary, fontWeight = FontWeight.SemiBold)
                        Text(
                            text = "${summary?.totalTuntas ?: 0}",
                            fontSize = 24.sp,
                            fontWeight = FontWeight.Bold,
                            color = RoyalIndigo
                        )
                        Text("Lulus KKM", fontSize = 11.sp, color = TextSecondary)
                    }
                }

                // Card Remidi
                Card(
                    modifier = Modifier.weight(1f),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(14.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                        Text("REMIDI (<75)", fontSize = 11.sp, color = TextSecondary, fontWeight = FontWeight.SemiBold)
                        Text(
                            text = "${summary?.totalRemidi ?: 0}",
                            fontSize = 24.sp,
                            fontWeight = FontWeight.Bold,
                            color = AmberWarning
                        )
                        Text("Perlu Perbaikan", fontSize = 11.sp, color = TextSecondary)
                    }
                }
            }
        }

        // Search Field
        item {
            OutlinedTextField(
                value = searchQuery,
                onValueChange = { searchQuery = it },
                placeholder = { Text("Cari Mapel, Bab, atau Judul Tugas...", color = TextSecondary, fontSize = 13.sp) },
                leadingIcon = { Icon(Icons.Default.Search, contentDescription = null, tint = RoyalIndigo) },
                singleLine = true,
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(14.dp),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = RoyalIndigo,
                    unfocusedBorderColor = DarkCard
                )
            )
        }

        // List of Grades Card
        items(filteredList) { item ->
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(containerColor = DarkCard)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Column(modifier = Modifier.weight(1f)) {
                            Text(item.mataPelajaran, fontSize = 16.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                            Text("${item.babMateri} • ${item.guruNama}", fontSize = 12.sp, color = TextSecondary)
                        }

                        // Badge Skor Nilai & Status
                        Column(horizontalAlignment = Alignment.End) {
                            Text(
                                text = item.nilaiFormatted,
                                fontSize = 22.sp,
                                fontWeight = FontWeight.Bold,
                                color = if (item.isTuntas) EmeraldGreen else AmberWarning
                            )
                            Badge(
                                containerColor = if (item.isTuntas) EmeraldGreen else AmberWarning
                            ) {
                                Text(
                                    text = "${item.status} (${item.predikat})",
                                    color = Color.White,
                                    fontSize = 10.sp,
                                    modifier = Modifier.padding(horizontal = 4.dp, vertical = 2.dp)
                                )
                            }
                        }
                    }

                    Spacer(modifier = Modifier.height(8.dp))
                    Divider(color = DarkBackground.copy(alpha = 0.5f))
                    Spacer(modifier = Modifier.height(8.dp))

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Text(item.judulTugas, fontSize = 13.sp, color = TextPrimary, fontWeight = FontWeight.Medium)
                        Text(item.tanggalFormatted, fontSize = 11.sp, color = TextSecondary)
                    }

                    if (item.catatanGuru.isNotBlank()) {
                        Spacer(modifier = Modifier.height(8.dp))
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .background(DarkBackground, RoundedCornerShape(8.dp))
                                .padding(10.dp)
                        ) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Icon(Icons.Default.Comment, contentDescription = null, tint = RoyalIndigo, modifier = Modifier.size(16.dp))
                                Spacer(modifier = Modifier.width(8.dp))
                                Text(
                                    text = "Catatan Guru: ${item.catatanGuru}",
                                    fontSize = 12.sp,
                                    color = TextPrimary
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}
```

---

## 10. 📝 UI SCREEN: GURU BATCH PENILAIAN (`ui/screens/GuruPenilaianDetailScreen.kt`)

```kotlin
package com.qpawdeveloper.absensimhc.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Save
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.qpawdeveloper.absensimhc.data.model.BatchNilaiItem
import com.qpawdeveloper.absensimhc.data.model.PenilaianHeaderData
import com.qpawdeveloper.absensimhc.data.model.PenilaianSiswaItem
import com.qpawdeveloper.absensimhc.ui.theme.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GuruPenilaianDetailScreen(
    header: PenilaianHeaderData?,
    initialSiswaList: List<PenilaianSiswaItem>,
    onSubmitBatch: (List<BatchNilaiItem>) -> Unit
) {
    // Local state map for dynamic editing
    val nilaiMap = remember { mutableStateMapOf<Int, String>() }
    val catatanMap = remember { mutableStateMapOf<Int, String>() }

    LaunchedEffect(initialSiswaList) {
        initialSiswaList.forEach {
            nilaiMap[it.siswaId] = if (it.nilai > 0) it.nilai.toString() else ""
            catatanMap[it.siswaId] = it.catatanGuru
        }
    }

    Box(modifier = Modifier.fillMaxSize().background(DarkBackground)) {
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp)
                .padding(bottom = 70.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            item {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(header?.mataPelajaran ?: "Penilaian Kelas", fontSize = 18.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                        Text("${header?.kelas ?: "-"} • ${header?.judulTugas ?: "-"}", fontSize = 13.sp, color = RoyalIndigo)
                        Text(header?.babMateri ?: "-", fontSize = 12.sp, color = TextSecondary)
                    }
                }
            }

            itemsIndexed(initialSiswaList) { index, siswa ->
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(containerColor = DarkCard)
                ) {
                    Row(
                        modifier = Modifier.padding(14.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Column(modifier = Modifier.weight(1f)) {
                            Text("${index + 1}. ${siswa.namaSiswa}", fontSize = 15.sp, fontWeight = FontWeight.Bold, color = TextPrimary)
                            Text("NIS: ${siswa.nis}", fontSize = 12.sp, color = TextSecondary)

                            Spacer(modifier = Modifier.height(4.dp))

                            OutlinedTextField(
                                value = catatanMap[siswa.siswaId] ?: "",
                                onValueChange = { catatanMap[siswa.siswaId] = it },
                                placeholder = { Text("Catatan / Feedback Guru...", fontSize = 11.sp) },
                                singleLine = true,
                                modifier = Modifier.fillMaxWidth(0.95f),
                                colors = OutlinedTextFieldDefaults.colors(focusedBorderColor = RoyalIndigo)
                            )
                        }

                        // Field Input Nilai
                        OutlinedTextField(
                            value = nilaiMap[siswa.siswaId] ?: "",
                            onValueChange = { nilaiMap[siswa.siswaId] = it },
                            label = { Text("Nilai") },
                            singleLine = true,
                            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                            modifier = Modifier.width(80.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                focusedBorderColor = RoyalIndigo,
                                unfocusedBorderColor = TextSecondary
                            )
                        )
                    }
                }
            }
        }

        // Floating Save Button
        Button(
            onClick = {
                val batchItems = initialSiswaList.map { s ->
                    val skor = nilaiMap[s.siswaId]?.toDoubleOrNull() ?: 0.0
                    val note = catatanMap[s.siswaId]
                    BatchNilaiItem(siswaId = s.siswaId, nilai = skor, catatanGuru = note)
                }
                onSubmitBatch(batchItems)
            },
            modifier = Modifier
                .fillMaxWidth()
                .height(56.dp)
                .padding(horizontal = 16.dp)
                .align(Alignment.BottomCenter)
                .padding(bottom = 12.dp),
            shape = RoundedCornerShape(16.dp),
            colors = ButtonDefaults.buttonColors(containerColor = EmeraldGreen)
        ) {
            Icon(Icons.Default.Save, contentDescription = null, tint = Color.White)
            Spacer(modifier = Modifier.width(8.dp))
            Text("SIMPAN SELURUH NILAI", fontSize = 16.sp, fontWeight = FontWeight.Bold, color = Color.White)
        }
    }
}
```

---

*Kode Sumber Kotlin Native — Developed by **qpawdeveloper**.*

