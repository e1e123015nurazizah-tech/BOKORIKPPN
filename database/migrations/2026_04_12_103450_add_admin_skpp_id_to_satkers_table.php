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
        Schema::table('satkers', function (Blueprint $table) {
            // Menambahkan kolom admin_skpp_id setelah nama_satker
            $table->unsignedBigInteger('admin_skpp_id')->nullable()->after('nama_satker');
            
            // Membuat foreign key yang merujuk ke tabel admins
            // (Asumsi nama tabel admin kamu adalah 'admins')
            $table->foreign('admin_skpp_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satkers', function (Blueprint $table) {
            // Hapus foreign key dulu, baru hapus kolomnya
            $table->dropForeign(['admin_skpp_id']);
            $table->dropColumn('admin_skpp_id');
        });
    }
};