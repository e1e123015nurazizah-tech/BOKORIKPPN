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
        // Menambahkan 'approver' ke dalam daftar ENUM menggunakan Raw SQL
        DB::statement("ALTER TABLE admins MODIFY COLUMN role ENUM('superadmin', 'operator', 'approver') DEFAULT 'operator'");
    }

    /**
     * Kembalikan ke asal jika di-rollback.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE admins MODIFY COLUMN role ENUM('superadmin', 'operator') DEFAULT 'operator'");
    }
};