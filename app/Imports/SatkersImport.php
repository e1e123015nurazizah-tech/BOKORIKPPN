<?php

namespace App\Imports;

use App\Models\Satker;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SatkersImport implements ToModel, WithHeadingRow
{
    // Variabel publik untuk menyimpan hasil hitungan agar bisa dibaca oleh Controller
    public $berhasil = 0;
    public $gagal = 0;

    public function model(array $row)
    {
        // 0. Jaring Pengaman: Jika baris kosong (tidak ada kode/nama), anggap gagal & skip
        if (empty($row['kode_satker']) || empty($row['nama_satker'])) {
            $this->gagal++;
            return null;
        }

        // 1. Cek apakah Kode Satker sudah ada (untuk menghindari error duplikat)
        $cekSatker = Satker::where('kode_satker', $row['kode_satker'])->first();
        if ($cekSatker) {
            $this->gagal++; // Tambah hitungan gagal/terlewat
            return null; // Skip jika sudah ada
        }

        // 2. LOGIKA AUTO-ASSIGN PETUGAS SKPP 
        $adminId = null;
        if (!empty($row['nama_petugas'])) {
            
            // TAHAP 1: Bersihkan spasi gaib dan ubah nama dari Excel jadi huruf KECIL semua
            // Contoh: "MeTa Brilian " akan diubah menjadi "meta brilian"
            $namaPencarian = strtolower(trim($row['nama_petugas']));

            // TAHAP 2: Gunakan whereRaw untuk memaksa MySQL mengecek database 
            $admin = Admin::whereRaw('LOWER(nama_lengkap) LIKE ?', ['%' . $namaPencarian . '%'])->first();
            
            if ($admin) {
                $adminId = $admin->id;
            }
        }

        // --- Jika lolos semua validasi di atas, berarti sukses ---
        $this->berhasil++; 

        // 3. Masukkan data ke Database
        return new Satker([
            'kode_satker'   => $row['kode_satker'],
            'nama_satker'   => strtoupper($row['nama_satker']), // Nama satker biarkan pakai huruf kapital
            'admin_skpp_id' => $adminId,
            'password'      => Hash::make($row['password'] ?? 'satker123'),
        ]);
    }
}