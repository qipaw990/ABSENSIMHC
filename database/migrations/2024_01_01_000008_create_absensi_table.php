<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('restrict');
            $table->foreignId('dicatat_oleh')->constrained('users')->onDelete('restrict');
            $table->date('tanggal');
            $table->time('jam_scan')->nullable()->comment('null jika status alpha/izin/sakit');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha']);
            $table->text('keterangan')->nullable();
            $table->string('lampiran', 255)->nullable()->comment('path file surat izin/sakit');
            $table->boolean('notif_terkirim')->default(false);
            $table->timestamps();

            // Satu siswa hanya 1 record absensi per hari
            $table->unique(['siswa_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
