<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TugasMateri extends Model
{
    use HasFactory;

    protected $table = 'tugas_materi';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mata_pelajaran_id',
        'jadwal_pelajaran_id',
        'mata_pelajaran',
        'bab_materi',
        'judul_tugas',
        'jenis',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function nilaiSiswa(): HasMany
    {
        return $this->hasMany(NilaiSiswa::class);
    }

    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis) {
            'tugas'     => 'Tugas Harian',
            'uh'        => 'Ulangan Harian',
            'uts'       => 'UTS / PTS',
            'uas'       => 'UAS / PAS',
            'praktikum' => 'Praktikum',
            default     => 'Tugas',
        };
    }
}
