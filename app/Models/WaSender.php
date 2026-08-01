<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class WaSender extends Model
{
    protected $table = 'wa_senders';

    protected $fillable = [
        'kelas_id',
        'nama_device',
        'token_fonnte',
        'nomor_wa',
        'status',
        'last_check_at',
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
    ];

    /**
     * Enkripsi token/API Key sebelum disimpan ke database.
     */
    public function setTokenFonnteAttribute(string $value): void
    {
        $this->attributes['token_fonnte'] = Crypt::encryptString($value);
    }

    /**
     * Dekripsi token/API Key saat dibaca dari database.
     */
    public function getTokenFonnteAttribute(?string $value): string
    {
        if (empty($value)) return '';
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Alias api_key untuk token_fonnte.
     */
    public function getApiKeyAttribute(): string
    {
        return $this->token_fonnte;
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->token_fonnte = $value;
    }

    /**
     * Helper untuk mendapatkan teks plain token_fonnte.
     */
    public function getTokenFonntePlainAttribute(): string
    {
        return $this->token_fonnte;
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waLogs(): HasMany
    {
        return $this->hasMany(WaLog::class);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aktif'    => '<span class="badge bg-success">Aktif</span>',
            'nonaktif' => '<span class="badge bg-secondary">Nonaktif</span>',
            'terputus' => '<span class="badge bg-danger">Terputus</span>',
            default    => '<span class="badge bg-warning">Unknown</span>',
        };
    }
}
