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
        Schema::create('detail_gajiwebs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->onDelete('cascade');
            $table->enum('jenis_pegawai', ['PNS', 'PPPK', 'POLRI', 'TNI']);
            $table->enum('jenis_proses', ['Rekon ADK', 'Penghapusan/Pembatalan ADK']);
            $table->enum('kategori_adk', [
                'ADK Gaji (.gpp)', 
                'ADK Penyamaan Data (.pgw / .kgw)', 
                'ADK Pegawai Baru (.krm / .bru / .kkk)', 
                'ADK Kelengkapan SK (.sk)', 
                'ADK Perbaikan NIP (.kor)', 
                'ADK Pegawai Baru (.krm / .bru / .kkk) yang masih gantung / belum disetujui', 
                'ADK Kelengkapan SK (.sk) yang masih gantung / belum disetujui', 
                'ADK Perbaikan NIP (.kor) yang masih gantung / belum disetujui'
            ]);
            $table->string('bulan_periode', 50);
            $table->string('file_kelengkapan'); 
            $table->text('catatan_satker')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_gajiwebs');
    }
};
