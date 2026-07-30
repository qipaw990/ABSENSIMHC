<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Helpers\AvatarHelper;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'nis',
        'nisn',
        'nama',
        'no_wa_ortu',
        'nama_ortu',
        'foto',
        'qr_token',
        'qr_is_active',
    ];

    protected $casts = [
        'qr_is_active' => 'boolean',
    ];

    /**
     * Generate QR token baru yang unik.
     */
    public static function generateQrToken(): string
    {
        do {
            $token = hash('sha256', Str::random(40) . time() . random_int(1000, 9999));
        } while (static::where('qr_token', $token)->exists());

        return $token;
    }

    /**
     * Regenerate QR: nonaktifkan token lama, buat token baru.
     */
    public function regenerateQr(): void
    {
        $this->update([
            'qr_token'      => static::generateQrToken(),
            'qr_is_active'  => true,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function izinSakit(): HasMany
    {
        return $this->hasMany(IzinSakit::class);
    }

    /**
     * Cek apakah siswa sudah absen hari ini.
     */
    public function sudahAbsenHariIni(): bool
    {
        return $this->absensi()
            ->whereDate('tanggal', today())
            ->exists();
    }

    /**
     * Ambil absensi hari ini.
     */
    public function absensiHariIni(): ?Absensi
    {
        return $this->absensi()
            ->whereDate('tanggal', today())
            ->first();
    }

    /**
     * Cek apakah ada izin/sakit yang disetujui untuk hari tertentu.
     */
    public function punyaIzinPadaTanggal(\Carbon\Carbon $tanggal): ?IzinSakit
    {
        return $this->izinSakit()
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tanggal->toDateString())
            ->where('tanggal_selesai', '>=', $tanggal->toDateString())
            ->first();
    }

    /**
     * URL foto siswa. Jika tidak ada foto → SVG avatar inisial nama.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }
        // Avatar SVG berbasis inisial — tidak butuh file eksternal
        return AvatarHelper::svgDataUri($this->nama);
    }

    public function getNoWaOrtuFormatAttribute(): string
    {
        $no = $this->no_wa_ortu;
        if (!$no) return '';
        // Pastikan format 62xxx
        if (str_starts_with($no, '0')) {
            $no = '62' . substr($no, 1);
        }
        return $no;
    }
}
