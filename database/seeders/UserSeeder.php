<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satker;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        // Langsung masukkan data Satker
        Satker::create([
            'nama_satker' => 'Korem 143/Ho',
            'kode_satker' => '123456',
            'password'    => Hash::make('password123'),
        ]);

        // Masukkan data Admin
        Admin::create([
            'nip'          => '199001012020011001',
            'nama_lengkap' => 'Nur Azizah',
            'jabatan'      => 'Administrator Sistem',
            'password'     => Hash::make('admin123'),
            'is_active'    => true,
        ]);
    }
}