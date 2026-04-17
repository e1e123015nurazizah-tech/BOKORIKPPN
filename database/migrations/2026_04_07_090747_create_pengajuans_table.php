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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 50)->unique();
            $table->string('nama_operator', 150);
            $table->string('no_whatsapp', 15);
            $table->enum('kategori_layanan', ['GajiWeb', 'PPNPN', 'SKPP']);
            $table->enum('status', ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'])->default('Menunggu');
            $table->text('catatan')->nullable(); 
            $table->dateTime('waktu_diambil')->nullable();
            // --- BAGIAN RELASI (FOREIGN KEY) ---
            // Relasi ke tabel satkers
            $table->foreignId('satker_id')
                  ->constrained('satkers')
                  ->restrictOnDelete()  
                  ->cascadeOnUpdate();
            // Relasi ke tabel admins
            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admins')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
