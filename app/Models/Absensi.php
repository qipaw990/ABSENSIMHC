<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'dicatat_oleh',
        'tanggal',
        'jam_scan',
        'status',
        'keterangan',
        'lampiran',
        'notif_terkirim',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'notif_terkirim' => 'boolean',
    ];

    // Konstanta status
    const STATUS_HADIR     = 'hadir';
    const STATUS_TERLAMBAT = 'terlambat';
    const STATUS_IZIN      = 'izin';
    const STATUS_SAKIT     = 'sakit';
    const STATUS_ALPHA     = 'alpha';

    public static array $statusLabels = [
        'hadir'     => 'Hadir',
        'terlambat' => 'Terlambat',
        'izin'      => 'Izin',
        'sakit'     => 'Sakit',
        'alpha'     => 'Alpha',
    ];

    public static array $statusColors = [
        'hadir'     => 'success',
        'terlambat' => 'warning',
        'izin'      => 'info',
        'sakit'     => 'primary',
        'alpha'     => 'danger',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function waLogs()
    {
        return $this->hasMany(WaLog::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return static::$statusColors[$this->status] ?? 'secondary';
    }

    public function getStatusBadgeAttribute(): string
    {
        $label = $this->status_label;
        $color = $this->status_color;
        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * Scope untuk filter berdasarkan tanggal hari ini.
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Scope untuk filter berdasarkan kelas.
     */
    public function scopeKelas($query, int $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }
}
