<?php

namespace App\Console\Commands;

use App\Models\WaSender;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class CheckWaSenderStatus extends Command
{
    protected $signature   = 'wa:check-sender-status';
    protected $description = 'Cek status/kesehatan semua device WA Fonnte per kelas';

    public function handle(FonnteService $fonnteService): int
    {
        $senders = WaSender::with('kelas')->get();

        if ($senders->isEmpty()) {
            $this->warn('Tidak ada WA Sender yang terdaftar.');
            return Command::SUCCESS;
        }

        $this->info("📡 Memeriksa {$senders->count()} WA Sender...");
        $this->newLine();

        $table = [];
        foreach ($senders as $sender) {
            $statusBaru = $fonnteService->cekStatus($sender->token_fonnte);
            $sender->update([
                'status'        => $statusBaru,
                'last_check_at' => now(),
            ]);

            $icon = match ($statusBaru) {
                'aktif'    => '✅',
                'terputus' => '❌',
                default    => '⚠️',
            };

            $table[] = [
                $sender->kelas->nama ?? '-',
                $sender->nama_device,
                $sender->nomor_wa ?? '-',
                $icon . ' ' . strtoupper($statusBaru),
            ];
        }

        $this->table(
            ['Kelas', 'Nama Device', 'Nomor WA', 'Status'],
            $table
        );

        $this->newLine();
        $this->info('✅ Pengecekan selesai.');
        return Command::SUCCESS;
    }
}
