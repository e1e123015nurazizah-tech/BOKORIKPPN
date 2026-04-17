<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    public function index()
    {
        // Proteksi Lapis Kedua: Pastikan Role-nya benar Superadmin
        if (Auth::guard('admin')->user()->role !== 'superadmin') {
            abort(403, 'Akses Ditolak: Hanya Superadmin yang boleh masuk.');
        }

        return view('admin.maintenance.index');
    }

    public function cleanup(Request $request)
    {
        if (Auth::guard('admin')->user()->role !== 'superadmin') {
            abort(403);
        }

        $request->validate([
            'tahun' => 'required|numeric',
            'mode' => 'required' // 'hanya_file' atau 'semua'
        ]);

        $tahun = $request->tahun;
        $mode = $request->mode;

        // Ambil data beserta relasi detailnya
        $pengajuans = Pengajuan::with(['detailGaji', 'detailPpnpn', 'detailSkpp'])
                                ->whereYear('created_at', $tahun)
                                ->get();

        if ($pengajuans->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tahun $tahun.");
        }

        // --- MULAI TRANSAKSI ---
        DB::beginTransaction();

        try {
            $count = 0;
            foreach ($pengajuans as $p) {
                // 1. Cari jalur file PDF di semua relasi detail
                $filePaths = [];
                if ($p->detailGaji) $filePaths[] = $p->detailGaji->file_kelengkapan;
                if ($p->detailPpnpn) $filePaths[] = $p->detailPpnpn->file_kelengkapan;
                if ($p->detailSkpp) $filePaths[] = $p->detailSkpp->file_kelengkapan;

                // 2. Hapus file fisik dari folder storage
                foreach ($filePaths as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                // 3. Eksekusi Database
                if ($mode == 'semua') {
                    $p->delete(); 
                } else {
                    // JANGAN gunakan NULL, gunakan string kosong '' agar database tidak protes
                    if ($p->detailGaji) $p->detailGaji->update(['file_kelengkapan' => '']);
                    if ($p->detailPpnpn) $p->detailPpnpn->update(['file_kelengkapan' => '']);
                    if ($p->detailSkpp) $p->detailSkpp->update(['file_kelengkapan' => '']);
                }
                $count++;
            }

            // Jika sampai sini tidak ada error, SIMPAN PERMANEN
            DB::commit();

            return back()->with('success', "Berhasil membersihkan $count data tahun $tahun dengan mode: " . ($mode == 'semua' ? 'Hapus Permanen' : 'Hapus File PDF'));

        } catch (\Exception $e) {
            // Jika terjadi SATU SAJA error, BATALKAN SEMUA PERUBAHAN DATABASE
            DB::rollBack();

            // Log errornya supaya kamu bisa cek di storage/logs/laravel.log
            Log::error("Gagal Cleanup Data: " . $e->getMessage());

            // Menampilkan error asli supaya kita tahu penyebab gagalnya saat testing
            return back()->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }
}