<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'semester',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Ambil tahun ajaran yang aktif saat ini.
     */
    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->nama . ' - ' . ucfirst($this->semester);
    }
}
