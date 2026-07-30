<?php

namespace Database\Seeders;

use App\Models\TemplatePesan;
use Illuminate\Database\Seeder;

class TemplatePesanSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'kode'     => 'hadir',
                'judul'    => 'Notifikasi Kehadiran',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n✅ Status: *HADIR*\n⏰ Pukul: {jam}\n📅 Tanggal: {tanggal}\n\nTerima kasih atas kepercayaan Bapak/Ibu kepada kami.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
            [
                'kode'     => 'terlambat',
                'judul'    => 'Notifikasi Keterlambatan',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n⚠️ Status: *TERLAMBAT*\n⏰ Tiba pukul: {jam}\n📅 Tanggal: {tanggal}\n\nMohon kerjasamanya untuk mengingatkan agar hadir tepat waktu.\n\nTerima kasih.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
            [
                'kode'     => 'alpha',
                'judul'    => 'Notifikasi Tidak Hadir (Alpha)',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n❌ Status: *TIDAK HADIR (ALPHA)*\n📅 Tanggal: {tanggal}\n\nHingga batas waktu absensi, yang bersangkutan belum hadir dan tidak ada keterangan.\n\nMohon segera menghubungi sekolah jika ada keperluan.\n\nTerima kasih.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
            [
                'kode'     => 'izin',
                'judul'    => 'Notifikasi Izin Disetujui',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa permohonan izin putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n📋 Status: *IZIN DISETUJUI*\n📅 Tanggal: {tanggal}\n📝 Keterangan: {keterangan}\n\nTerima kasih.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
            [
                'kode'     => 'sakit',
                'judul'    => 'Notifikasi Sakit',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa keterangan sakit putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n🏥 Status: *SAKIT*\n📅 Tanggal: {tanggal}\n📝 Keterangan: {keterangan}\n\nSemoga segera pulih. Terima kasih.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
            [
                'kode'     => 'izin_ditolak',
                'judul'    => 'Notifikasi Izin Ditolak',
                'template' => "Assalamu'alaikum Wr. Wb.\n\nYth. Bapak/Ibu {nama_ortu},\n\nKami informasikan bahwa permohonan izin putra/putri Bapak/Ibu:\n\n👤 *{nama_siswa}*\n🏫 Kelas: {kelas}\n❌ Status: *IZIN DITOLAK*\n📅 Tanggal: {tanggal}\n📝 Catatan: {keterangan}\n\nUntuk informasi lebih lanjut, silakan hubungi wali kelas.\n\nTerima kasih.\n\nSalam,\n{nama_sekolah}",
                'is_aktif' => true,
            ],
        ];

        foreach ($templates as $template) {
            TemplatePesan::updateOrCreate(
                ['kode' => $template['kode']],
                $template
            );
        }

        $this->command->info('✅ Template pesan WhatsApp berhasil dibuat (' . count($templates) . ' template)');
    }
}
