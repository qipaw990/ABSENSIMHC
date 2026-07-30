<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaLog extends Model
{
    protected $table = 'wa_logs';

    protected $fillable = [
        'wa_sender_id',
        'absensi_id',
        'target_nomor',
        'pesan',
        'status',
        'response_fonnte',
        'retry_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'retry_count' => 'integer',
    ];

    public function waSender(): BelongsTo
    {
        return $this->belongsTo(WaSender::class);
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'terkirim' => '<span class="badge bg-success">Terkirim</span>',
            'antrian'  => '<span class="badge bg-warning text-dark">Antrian</span>',
            'gagal'    => '<span class="badge bg-danger">Gagal</span>',
            default    => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }
}
