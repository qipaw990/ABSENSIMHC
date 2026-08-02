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
        Schema::table('tugas_materi', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')->nullable()->after('kelas_id')->constrained('mata_pelajaran')->nullOnDelete();
            $table->foreignId('jadwal_pelajaran_id')->nullable()->after('mata_pelajaran_id')->constrained('jadwal_pelajaran')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_materi', function (Blueprint $table) {
            $table->dropForeign(['jadwal_pelajaran_id']);
            $table->dropForeign(['mata_pelajaran_id']);
            $table->dropColumn(['jadwal_pelajaran_id', 'mata_pelajaran_id']);
        });
    }
};
