<?php

namespace App\Imports;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cek Duplikat NIP
        $cekAdmin = Admin::where('nip', $row['nip'])->first();
        if ($cekAdmin) {
            return null; // Skip jika NIP sudah ada
        }

        // 2. Validasi Role (Jika salah ketik di Excel, jadikan operator)
        $role = strtolower($row['role'] ?? 'operator');
        if (!in_array($role, ['superadmin', 'operator', 'approver'])) {
            $role = 'operator';
        }

        // 3. Masukkan ke Database
        return new Admin([
            'nip'          => $row['nip'],
            'nama_lengkap' => strtoupper($row['nama_lengkap']),
            'jabatan'      => strtoupper($row['jabatan']),
            'role'         => $role,
            'password'     => Hash::make($row['password'] ?? 'bokori123'),
            'is_active'    => true, // Otomatis aktif
        ]);
    }
}