<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_sender_id')->constrained('wa_senders')->onDelete('restrict');
            $table->foreignId('absensi_id')->nullable()->constrained('absensi')->onDelete('set null');
            $table->string('target_nomor', 20);
            $table->text('pesan');
            $table->enum('status', ['antrian', 'terkirim', 'gagal'])->default('antrian');
            $table->text('response_fonnte')->nullable()->comment('raw response dari API Fonnte');
            $table->tinyInteger('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['wa_sender_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_logs');
    }
};
