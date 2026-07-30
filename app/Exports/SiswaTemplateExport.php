<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
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

class SiswaTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Data Siswa';
    }

    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'nama',
            'kelas',
            'no_wa_ortu',
            'nama_ortu',
        ];
    }

    public function array(): array
    {
        // Contoh data (baris ke-2 dan ke-3 sebagai panduan)
        return [
            ['1001', '9901234567', 'CONTOH NAMA SISWA', 'XII RPL 1', '628123456789', 'NAMA ORANG TUA'],
            ['1002', '9901234568', 'CONTOH NAMA SISWA 2', 'XII RPL 1', '628987654321', 'NAMA ORANG TUA 2'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // nis
            'B' => 18,  // nisn
            'C' => 35,  // nama
            'D' => 20,  // kelas
            'E' => 20,  // no_wa_ortu
            'F' => 30,  // nama_ortu
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row (baris 1)
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e1b4b']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ],
            // Contoh data (baris 2 & 3)
            '2:3' => [
                'font'      => ['color' => ['rgb' => '374151']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f0ff']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Tinggi baris header
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Freeze pane setelah header agar mudah scroll
                $sheet->freezePane('A2');

                // Tambahkan keterangan di bawah data contoh
                $sheet->setCellValue('A5', '⚠️ PETUNJUK PENGISIAN:');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('991b1b');

                $petunjuk = [
                    ['A6', 'nis', 'Nomor Induk Siswa — WAJIB DIISI, harus unik'],
                    ['A7', 'nisn', 'Nomor Induk Siswa Nasional — opsional'],
                    ['A8', 'nama', 'Nama lengkap siswa — WAJIB DIISI, gunakan HURUF KAPITAL'],
                    ['A9', 'kelas', 'Nama kelas persis seperti di sistem, contoh: XII RPL 1'],
                    ['A10', 'no_wa_ortu', 'Nomor WA orang tua, contoh: 628123456789 (awali 62, tanpa tanda + atau spasi)'],
                    ['A11', 'nama_ortu', 'Nama orang tua — opsional'],
                    ['A12', '* Baris contoh (baris 2 & 3)', 'dapat dihapus sebelum diupload'],
                ];

                foreach ($petunjuk as [$cell, $kolom, $keterangan]) {
                    $sheet->setCellValue($cell, '• ' . $kolom . ' : ' . $keterangan);
                    $sheet->getStyle($cell)->getFont()->setSize(9)->getColor()->setRGB('374151');
                    $sheet->mergeCells($cell . ':F' . substr($cell, 1));
                }

                // Border untuk area data header
                $sheet->getStyle('A1:F1')->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_MEDIUM);
            },
        ];
    }
}
