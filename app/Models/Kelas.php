<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'tahun_ajaran_id',
        'wali_kelas_id',
        'nama',
        'tingkat',
    ];

    protected $casts = [
        'tingkat' => 'integer',
    ];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function waSender(): HasOne
    {
        return $this->hasOne(WaSender::class);
    }

    public function pengaturanAbsensi(): HasOne
    {
        return $this->hasOne(PengaturanAbsensi::class);
    }

    /**
     * Ambil pengaturan absensi kelas ini, atau fallback ke default global.
     */
    public function getPengaturanAttribute(): PengaturanAbsensi
    {
        return $this->pengaturanAbsensi
            ?? PengaturanAbsensi::whereNull('kelas_id')->first()
            ?? new PengaturanAbsensi([
                'jam_masuk_batas'   => '07:15:00',
                'jam_absensi_tutup' => '08:00:00',
                'aktif_sabtu'       => false,
            ]);
    }

    public function getNamaLengkapAttribute(): string
    {
        return 'Kelas ' . $this->nama;
    }
}
