<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_pesan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique()->comment('hadir, terlambat, alpha, izin_disetujui, izin_ditolak, sakit_disetujui');
            $table->string('judul', 100);
            $table->text('template')->comment('Gunakan placeholder: {nama_siswa}, {nama_ortu}, {jam}, {tanggal}, {status}, {nama_sekolah}, {kelas}, {keterangan}');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_pesan');
    }
};
