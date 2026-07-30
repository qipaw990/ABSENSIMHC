<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('restrict');
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nama', 100);
            $table->string('no_wa_ortu', 20)->nullable()->comment('format: 62xxxxxxxxx');
            $table->string('nama_ortu', 100)->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('qr_token', 64)->unique()->comment('token unik untuk QR, bukan NIS');
            $table->boolean('qr_is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
