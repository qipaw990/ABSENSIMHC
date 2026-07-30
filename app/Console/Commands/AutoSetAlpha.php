<?php

namespace App\Console\Commands;

use App\Services\AbsensiService;
use Illuminate\Console\Command;

class AutoSetAlpha extends Command
{
    protected $signature   = 'absensi:auto-alpha {--tanggal= : Tanggal target format Y-m-d (default: hari ini)}';
    protected $description = 'Set status Alpha otomatis untuk siswa yang belum absen setelah jam absensi ditutup';

    public function handle(AbsensiService $absensiService): int
    {
        $tanggalInput = $this->option('tanggal');
        $tanggal = $tanggalInput
            ? \Carbon\Carbon::parse($tanggalInput)
            : today();

        $this->info("🔍 Memproses auto-alpha untuk tanggal: {$tanggal->toDateString()}");

        $jumlah = $absensiService->autoSetAlpha($tanggal);

        $this->info("✅ Selesai: {$jumlah} siswa di-set status Alpha.");
        return Command::SUCCESS;
    }
}
