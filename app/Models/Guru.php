<?php

namespace App\Models;

use App\Helpers\AvatarHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'no_wa',
        'foto',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function absensiDicatat(): HasMany
    {
        return $this->hasMany(Absensi::class, 'dicatat_oleh');
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    /**
     * URL foto guru. Jika tidak ada foto → SVG avatar inisial nama.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }
        // Warna avatar konsisten berdasarkan nama — tidak butuh file eksternal
        return AvatarHelper::svgDataUri($this->nama);
    }
}
