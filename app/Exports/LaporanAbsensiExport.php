<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LaporanAbsensiExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected Kelas  $kelas;
    protected array  $siswaList;
    protected Carbon $tanggalMulai;
    protected int    $jumlahHari;
    protected string $namaSekolah;

    public function __construct(Kelas $kelas, array $siswaList, Carbon $tanggalMulai, int $jumlahHari, string $namaSekolah)
    {
        $this->kelas        = $kelas;
        $this->siswaList    = $siswaList;
        $this->tanggalMulai = $tanggalMulai;
        $this->jumlahHari   = $jumlahHari;
        $this->namaSekolah  = $namaSekolah;
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }

    public function headings(): array
    {
        $days = [];
        for ($d = 1; $d <= $this->jumlahHari; $d++) {
            $days[] = $d;
        }

        return array_merge(
            ['No', 'NIS', 'Nama Siswa'],
            $days,
            ['H', 'T', 'I', 'S', 'A', 'Kehadiran (%)']
        );
    }

    public function collection(): Collection
    {
        $rows = [];
        foreach ($this->siswaList as $i => $item) {
            $siswa = $item['siswa'];
            $row   = [$i + 1, $siswa->nis, $siswa->nama];

            for ($d = 1; $d <= $this->jumlahHari; $d++) {
                $st   = $item['daily'][$d] ?? null;
                $code = match($st) {
                    'hadir'     => 'H',
                    'terlambat' => 'T',
                    'izin'      => 'I',
                    'sakit'     => 'S',
                    'alpha'     => 'A',
                    default     => '-',
                };
                $row[] = $code;
            }

            $hadir = $item['hadir'] ?? 0;
            $telat = $item['terlambat'] ?? 0;
            $izin  = $item['izin'] ?? 0;
            $sakit = $item['sakit'] ?? 0;
            $alpha = $item['alpha'] ?? 0;
            $total = $hadir + $telat + $izin + $sakit + $alpha;
            $pct   = $total > 0 ? round((($hadir + $telat) / $total) * 100) : 0;

            array_push($row, $hadir, $telat, $izin, $sakit, $alpha, $pct . '%');
            $rows[] = $row;
        }

        return collect($rows);
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 5, 'B' => 14, 'C' => 28];
        $cols   = range('D', 'Z');
        $extras = ['AA', 'AB', 'AC', 'AD', 'AE'];
        $allCols = array_merge($cols, $extras);

        // Hari (kolom D dst)
        for ($i = 0; $i < $this->jumlahHari; $i++) {
            if (isset($allCols[$i])) {
                $widths[$allCols[$i]] = 4;
            }
        }

        // Rekap (H T I S A %)
        $rekapStart = $this->jumlahHari;
        for ($i = 0; $i < 6; $i++) {
            if (isset($allCols[$rekapStart + $i])) {
                $widths[$allCols[$rekapStart + $i]] = $i === 5 ? 10 : 5;
            }
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e1b4b']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338ca']]],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = count($this->siswaList) + 1;
                $lastCol  = 3 + $this->jumlahHari + 6;
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);

                // Freeze panel setelah kolom nama
                $sheet->freezePane('D2');

                // Baris header tinggi
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Row header info di atas tabel (baris info sekolah)
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', strtoupper($this->namaSekolah));
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI ABSENSI HARIAN SISWA');
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $periode = 'Kelas: ' . $this->kelas->nama . ' | Periode: ' . $this->tanggalMulai->translatedFormat('F Y');
                $sheet->setCellValue('A3', $periode);
                $sheet->mergeCells("A3:{$lastColLetter}3");
                $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('475569');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Border untuk data rows
                $dataRange = "A4:{$lastColLetter}" . ($lastRow + 3);
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('cbd5e1');

                // Center align semua kolom hari & rekap
                $dayStart = 'D';
                $sheet->getStyle("{$dayStart}4:{$lastColLetter}" . ($lastRow + 3))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Zebra striping
                for ($row = 4; $row <= $lastRow + 3; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('f8fafc');
                    }
                }
            },
        ];
    }
}
