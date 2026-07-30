<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->nullable()->unique()->constrained('kelas')->onDelete('cascade');
            $table->time('jam_masuk_batas')->default('07:15:00')->comment('Sebelum ini = Tepat Waktu');
            $table->time('jam_absensi_tutup')->default('08:00:00')->comment('Setelah ini = Auto Alpha');
            $table->boolean('aktif_sabtu')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_absensi');
    }
};
