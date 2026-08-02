<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode',
        'nama',
        'kelompok',
        'keterangan',
    ];

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function getKelompokLabelAttribute(): string
    {
        return match($this->kelompok) {
            'normatif'    => 'Normatif',
            'adaptif'     => 'Adaptif',
            'produktif'   => 'Produktif / Kejuruan',
            'muatan_lokal'=> 'Muatan Lokal',
            default       => 'Umum',
        };
    }
}
