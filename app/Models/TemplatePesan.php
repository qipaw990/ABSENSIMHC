<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePesan extends Model
{
    protected $table = 'template_pesan';

    protected $fillable = [
        'kode',
        'judul',
        'template',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Render template dengan mengganti placeholder dengan data nyata.
     *
     * @param array $data Contoh: ['nama_siswa' => 'Budi', 'jam' => '07:10', ...]
     */
    public function render(array $data): string
    {
        $pesan = $this->template;
        foreach ($data as $key => $value) {
            $pesan = str_replace('{' . $key . '}', $value, $pesan);
        }
        return $pesan;
    }

    /**
     * Ambil template berdasarkan kode, atau null jika tidak ada/tidak aktif.
     */
    public static function aktif(string $kode): ?self
    {
        return static::where('kode', $kode)
            ->where('is_aktif', true)
            ->first();
    }
}
