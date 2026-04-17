<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan perintah untuk mengubah ENUM.
     */
    public function up(): void
    {
        // Menambahkan 'Menunggu Approval' ke dalam daftar ENUM
        DB::statement("ALTER TABLE pengajuans MODIFY COLUMN status ENUM('Menunggu', 'Diproses', 'Menunggu Approval', 'Selesai', 'Ditolak') DEFAULT 'Menunggu'");
    }

    /**
     * Kembalikan ke asal jika di-rollback.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pengajuans MODIFY COLUMN status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Menunggu'");
    }
};