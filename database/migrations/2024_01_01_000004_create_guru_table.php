<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nip', 20)->nullable()->unique();
            $table->string('nama', 100);
            $table->string('no_wa', 20)->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamps();
        });

        // Tambah FK wali_kelas_id ke kelas setelah tabel guru ada
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('wali_kelas_id')->references('id')->on('guru')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['wali_kelas_id']);
        });
        Schema::dropIfExists('guru');
    }
};
