<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public array $importErrors = [];
    public int   $imported     = 0;
    public int   $skipped      = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // baris Excel mulai dari 2 (setelah heading)

            try {
                // Ambil nilai dari kolom (support nama kolom fleksibel)
                $nis       = trim((string) ($row['nis'] ?? $row['no_induk'] ?? ''));
                $nisn      = trim((string) ($row['nisn'] ?? ''));
                $nama      = trim((string) ($row['nama'] ?? $row['nama_siswa'] ?? ''));
                $kelasNama = trim((string) ($row['kelas'] ?? $row['nama_kelas'] ?? ''));
                $noWaOrtu  = trim((string) ($row['no_wa_ortu'] ?? $row['wa_ortu'] ?? $row['hp_ortu'] ?? ''));
                $namaOrtu  = trim((string) ($row['nama_ortu'] ?? $row['orang_tua'] ?? ''));

                // Skip baris kosong
                if (empty($nis) || empty($nama)) {
                    $this->skipped++;
                    continue;
                }

                // Skip NIS yang sudah ada
                if (Siswa::where('nis', $nis)->exists()) {
                    $this->importErrors[] = "Baris {$rowNum}: NIS {$nis} ({$nama}) sudah terdaftar — dilewati.";
                    $this->skipped++;
                    continue;
                }

                // Cari kelas berdasarkan nama
                $kelas = null;
                if ($kelasNama) {
                    $kelas = Kelas::where('nama', 'like', '%' . $kelasNama . '%')->first();
                    if (!$kelas) {
                        $this->importErrors[] = "Baris {$rowNum}: Kelas '{$kelasNama}' untuk siswa {$nama} tidak ditemukan — dilewati.";
                        $this->skipped++;
                        continue;
                    }
                }

                // Format nomor WA (pastikan diawali 62)
                if ($noWaOrtu) {
                    $noWaOrtu = preg_replace('/\D/', '', $noWaOrtu);
                    if (str_starts_with($noWaOrtu, '0')) {
                        $noWaOrtu = '62' . substr($noWaOrtu, 1);
                    } elseif (!str_starts_with($noWaOrtu, '62')) {
                        $noWaOrtu = '62' . $noWaOrtu;
                    }
                }

                // Buat akun user siswa
                $user = User::create([
                    'name'     => $nama,
                    'email'    => $nis . '@siswa.sch.id',
                    'password' => Hash::make($nis),
                ]);
                $user->assignRole('siswa');

                // Buat data siswa
                Siswa::create([
                    'nis'        => $nis,
                    'nisn'       => $nisn ?: null,
                    'nama'       => $nama,
                    'kelas_id'   => $kelas?->id,
                    'no_wa_ortu' => $noWaOrtu ?: null,
                    'nama_ortu'  => $namaOrtu ?: null,
                    'user_id'    => $user->id,
                    'qr_token'   => Siswa::generateQrToken(),
                ]);

                $this->imported++;

            } catch (Throwable $e) {
                $this->importErrors[] = "Baris {$rowNum}: Error — " . $e->getMessage();
                $this->skipped++;
            }
        }
    }
}
