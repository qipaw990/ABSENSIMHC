<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel Tugas / Materi Pembelajaran
        Schema::create('tugas_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('mata_pelajaran');
            $table->string('bab_materi');
            $table->string('judul_tugas');
            $table->enum('jenis', ['tugas', 'uh', 'uts', 'uas', 'praktikum'])->default('tugas');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Tabel Nilai Siswa per Tugas/Materi
        Schema::create('nilai_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_materi_id')->constrained('tugas_materi')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->default(0);
            $table->string('catatan_guru')->nullable();
            $table->timestamps();

            $table->unique(['tugas_materi_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_siswa');
        Schema::dropIfExists('tugas_materi');
    }
};
