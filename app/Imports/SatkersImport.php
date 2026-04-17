<?php

namespace App\Imports;

use App\Models\Satker;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SatkersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cek apakah Kode Satker sudah ada (untuk menghindari error duplikat)
        $cekSatker = Satker::where('kode_satker', $row['kode_satker'])->first();
        if ($cekSatker) {
            return null; // Skip jika sudah ada
        }

        // 2. Logika Auto-Assign Petugas SKPP berdasarkan Nama Petugas di Excel
        $adminId = null;
        if (!empty($row['nama_petugas'])) {
            $admin = Admin::where('nama_lengkap', 'LIKE', '%' . $row['nama_petugas'] . '%')->first();
            if ($admin) {
                $adminId = $admin->id;
            }
        }

        // 3. Masukkan data ke Database
        return new Satker([
            'kode_satker'   => $row['kode_satker'],
            'nama_satker'   => strtoupper($row['nama_satker']),
            'admin_skpp_id' => $adminId,
            'password'      => Hash::make($row['password'] ?? 'satker123'),
        ]);
    }
}