<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_senders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->unique()->constrained('kelas')->onDelete('cascade');
            $table->string('nama_device', 100)->comment('contoh: WA XII RPL 1');
            $table->text('token_fonnte')->comment('TERENKRIPSI dengan Laravel Crypt');
            $table->string('nomor_wa', 20)->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'terputus'])->default('nonaktif');
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_senders');
    }
};
