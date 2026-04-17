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
        Schema::create('detail_skpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->onDelete('cascade');
            $table->string('anak_satker', 150);
            $table->enum('jenis_pegawai', ['PNS', 'TNI', 'POLRI', 'PPPK']);
            $table->string('id_skpp', 50);
            $table->enum('jenis_skpp', ['Pindah', 'Pensiun', 'Berhenti Non Pensiun', 'Meninggal Berhak Pensiun', 'Meninggal Tidak Berhak Pensiun']);
            $table->string('nomor_skpp', 100);
            $table->string('nama_pegawai', 150);
            $table->integer('jumlah_pegawai');
            $table->string('bulan_periode', 50);
            $table->string('file_kelengkapan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_skpps');
    }
};
