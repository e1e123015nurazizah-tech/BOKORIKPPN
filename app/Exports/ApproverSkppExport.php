<?php

namespace App\Exports;

use App\Models\Pengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApproverSkppExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tahun;

    public function __construct($tahun) {
        $this->tahun = $tahun;
    }

    public function collection() {
        // DISINI KUNCINYA: Hanya ambil kategori SKPP
        return Pengajuan::with(['satker', 'admin'])
            ->where('kategori_layanan', 'SKPP') 
            ->whereYear('created_at', $this->tahun)
            ->latest()
            ->get();
    }

    public function headings(): array {
        return ['Nomor Tiket', 'Kode Satker', 'Nama Satuan Kerja', 'Status', 'Petugas Pemeriksa', 'Waktu Pengajuan'];
    }

    public function map($item): array {
        return [
            $item->nomor_tiket,
            "'" . ($item->satker->kode_satker ?? '-'),
            $item->satker->nama_satker ?? '-',
            $item->status,
            $item->admin->nama_lengkap ?? '-',
            $item->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet) {
        return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']]]];
    }
}