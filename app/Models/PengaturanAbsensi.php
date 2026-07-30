<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanAbsensi extends Model
{
    protected $table = 'pengaturan_absensi';

    protected $fillable = [
        'kelas_id',
        'jam_masuk_batas',
        'jam_absensi_tutup',
        'aktif_sabtu',
    ];

    protected $casts = [
        'aktif_sabtu' => 'boolean',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Tentukan status kehadiran berdasarkan jam scan.
     *
     * @param string $jamScan Format "H:i:s"
     * @return string 'hadir' | 'terlambat'
     */
    public function tentukanStatus(string $jamScan): string
    {
        return $jamScan <= $this->jam_masuk_batas ? 'hadir' : 'terlambat';
    }

    /**
     * Cek apakah masih dalam jam absensi.
     */
    public function masihDalamJamAbsensi(): bool
    {
        return now()->format('H:i:s') <= $this->jam_absensi_tutup;
    }
}
