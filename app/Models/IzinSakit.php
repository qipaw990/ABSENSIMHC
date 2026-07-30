<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinSakit extends Model
{
    protected $table = 'izin_sakit';

    protected $fillable = [
        'siswa_id',
        'disetujui_oleh',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'keterangan',
        'lampiran',
        'status',
        'catatan_penolakan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDisetujui(): bool
    {
        return $this->status === 'disetujui';
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            default => ucfirst($this->jenis),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => '<span class="badge bg-warning text-dark">Menunggu</span>',
            'disetujui' => '<span class="badge bg-success">Disetujui</span>',
            'ditolak'   => '<span class="badge bg-danger">Ditolak</span>',
            default     => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    /**
     * Jumlah hari izin/sakit.
     */
    public function getJumlahHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }
}
