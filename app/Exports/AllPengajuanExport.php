<?php

namespace App\Exports;

use App\Models\Pengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllPengajuanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection()
    {
        // Ambil SEMUA jenis pengajuan berdasarkan tahun yang dipilih di pojok kanan
        return Pengajuan::with(['satker', 'admin'])
            ->whereYear('created_at', $this->tahun)
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Waktu Pengajuan',
            'Nomor Tiket',
            'Layanan / Jenis Pengajuan',
            'Kode Satker',
            'Nama Satuan Kerja',
            'Status Akhir',
            'Petugas Pemeriksa (Admin)',
            'Catatan / Feedback',
        ];
    }

    public function map($item): array
    {
        return [
            $item->created_at->format('d/m/Y H:i:s'),
            $item->nomor_tiket,
            $item->kategori_layanan, // Ini untuk membedakan GajiWeb, SKPP, atau PPNPN
            "'" . ($item->satker->kode_satker ?? '-'), // Petik satu agar nol depan tidak hilang
            $item->satker->nama_satker ?? '-',
            $item->status,
            $item->admin->nama_lengkap ?? 'Belum Diperiksa',
            $item->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']]
            ],
        ];
    }
}