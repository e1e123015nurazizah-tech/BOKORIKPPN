<?php

namespace App\Exports;

use App\Models\Satker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SatkersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        // Ambil semua data Satker beserta relasi Petugas SKPP-nya
        return Satker::with('petugasSkpp')->latest()->get();
    }

    public function map($satker): array
    {
        return [
            // Tambahkan tanda petik tunggal agar Excel membacanya sebagai teks murni (angka nol di depan tidak hilang)
            "'" . $satker->kode_satker,
            $satker->nama_satker,
            // Jika ada petugas, tampilkan namanya. Jika tidak ada, isi dengan tanda strip (-)
            $satker->petugasSkpp ? $satker->petugasSkpp->nama_lengkap : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Satker',
            'Nama Satker',
            'Petugas SKPP',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header): Font tebal, warna teks putih, background biru
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']]
            ],
        ];
    }
}