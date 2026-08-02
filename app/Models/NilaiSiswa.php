<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSiswa extends Model
{
    use HasFactory;

    protected $table = 'nilai_siswa';

    protected $fillable = [
        'tugas_materi_id',
        'siswa_id',
        'nilai',
        'catatan_guru',
    ];

    public function tugasMateri(): BelongsTo
    {
        return $this->belongsTo(TugasMateri::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
