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
        Schema::create('detail_ppnpns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->onDelete('cascade');
            $table->string('kode_anak_satker', 50);
            $table->enum('jenis_adk', ['Pengajuan Baru', 'Pembatalan/Penghapusan']);
            $table->string('id_adk', 20);
            $table->date('tanggal_antrean');
            $table->string('bulan_periode', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_ppnpns');
    }
};
