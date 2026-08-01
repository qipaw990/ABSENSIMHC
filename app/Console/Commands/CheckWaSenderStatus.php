<?php

namespace App\Console\Commands;

use App\Models\WaSender;
use App\Services\WaGatewayService;
use Illuminate\Console\Command;

class CheckWaSenderStatus extends Command
{
    protected $signature   = 'wa:check-sender-status';
    protected $description = 'Cek status/kesehatan semua device WA Gateway per kelas';

    public function handle(WaGatewayService $waGatewayService): int
    {
        $senders = WaSender::with('kelas')->get();

        if ($senders->isEmpty()) {
            $this->warn('Tidak ada WA Sender yang terdaftar.');
            return Command::SUCCESS;
        }

        $this->info("📡 Memeriksa {$senders->count()} WA Sender via WhatsApp Gateway...");
        $this->newLine();

        $table = [];
        foreach ($senders as $sender) {
            $statusBaru = $waGatewayService->cekStatus($sender->api_key);
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
