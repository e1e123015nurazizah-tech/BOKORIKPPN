<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan; // Pastikan Model Pengajuan di-import
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // ============================================================
        // 0. PENGALIHAN KHUSUS UNTUK APPROVER (PAK AGUNG)
        // Jika yang login adalah Approver, lempar ke menu Semua Data
        // ============================================================
        if (Auth::guard('admin')->user()->role === 'approver') {
            return redirect()->route('admin.pengajuan.index');
        }

        // --- TANGKAP MEMORI TAHUN DARI SESSION ---
        $tahunAktif = session('tahun_aktif', date('Y'));

        // ============================================================
        // 1. HITUNG VOLUME UTAMA (Seluruh Satker)
        // ============================================================
        $volumeGaji = Pengajuan::where('kategori_layanan', 'GajiWeb')
                        ->whereYear('created_at', $tahunAktif)->count();

        $volumePPNPN = Pengajuan::where('kategori_layanan', 'PPNPN')
                        ->whereYear('created_at', $tahunAktif)->count();

        $volumeSKPP = Pengajuan::where('kategori_layanan', 'SKPP')
                        ->whereYear('created_at', $tahunAktif)->count();

        // ============================================================
        // 2. HITUNG STATUS GLOBAL
        // ============================================================
        $statusMenunggu = Pengajuan::where('status', 'Menunggu')
                            ->whereYear('created_at', $tahunAktif)->count();
        $statusDiproses = Pengajuan::where('status', 'Diproses')
                            ->whereYear('created_at', $tahunAktif)->count();
        $statusSelesai  = Pengajuan::where('status', 'Selesai')
                            ->whereYear('created_at', $tahunAktif)->count();
        $statusDitolak  = Pengajuan::where('status', 'Ditolak')
                            ->whereYear('created_at', $tahunAktif)->count();

        // ============================================================
        // 3. DATA GRAFIK REKON GAJI (Seluruh Satker)
        // ============================================================
        $gajiPNS = Pengajuan::where('kategori_layanan', 'GajiWeb')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'PNS'); })->count();
        $gajiPPPK = Pengajuan::where('kategori_layanan', 'GajiWeb')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'PPPK'); })->count();
        $gajiPOLRI = Pengajuan::where('kategori_layanan', 'GajiWeb')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'POLRI'); })->count();
        $gajiTNI = Pengajuan::where('kategori_layanan', 'GajiWeb')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'TNI'); })->count();

        // ============================================================
        // 4. DATA GRAFIK PPNPN (Sesuaikan nama kolom: jenis_adk)
        // ============================================================
        $ppnpnBaru = Pengajuan::where('kategori_layanan', 'PPNPN')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailPpnpn', function($q){ $q->where('jenis_adk', 'Pengajuan Baru'); })->count();
        $ppnpnBatal = Pengajuan::where('kategori_layanan', 'PPNPN')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailPpnpn', function($q){ $q->where('jenis_adk', 'Pembatalan/Penghapusan'); })->count();

        // ============================================================
        // 5. DATA GRAFIK SKPP (Seluruh Satker)
        // ============================================================
        $skppPNS = Pengajuan::where('kategori_layanan', 'SKPP')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'PNS'); })->count();
        $skppPPPK = Pengajuan::where('kategori_layanan', 'SKPP')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'PPPK'); })->count();
        $skppTNI = Pengajuan::where('kategori_layanan', 'SKPP')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'TNI'); })->count();
        $skppPOLRI = Pengajuan::where('kategori_layanan', 'SKPP')
                    ->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'POLRI'); })->count();

        // ============================================================
        // 6. TREN BULANAN (Seluruh Satker)
        // ============================================================
        $trenPNS = array_fill(0, 12, 0);
        $trenPPPK = array_fill(0, 12, 0);
        $trenPOLRI = array_fill(0, 12, 0);
        $trenTNI = array_fill(0, 12, 0);

        $pengajuansTren = Pengajuan::with(['detailGaji', 'detailSkpp'])
            ->whereYear('created_at', $tahunAktif)
            ->whereIn('kategori_layanan', ['GajiWeb', 'SKPP'])
            ->get();

        foreach ($pengajuansTren as $p) {
            $bulanIndex = $p->created_at->format('n') - 1;
            $jenisPegawai = null;

            if ($p->kategori_layanan == 'GajiWeb' && $p->detailGaji) {
                $jenisPegawai = $p->detailGaji->jenis_pegawai;
            } elseif ($p->kategori_layanan == 'SKPP' && $p->detailSkpp) {
                $jenisPegawai = $p->detailSkpp->jenis_pegawai;
            }

            if ($jenisPegawai == 'PNS') $trenPNS[$bulanIndex]++;
            elseif ($jenisPegawai == 'PPPK') $trenPPPK[$bulanIndex]++;
            elseif ($jenisPegawai == 'POLRI') $trenPOLRI[$bulanIndex]++;
            elseif ($jenisPegawai == 'TNI') $trenTNI[$bulanIndex]++;
        }

        // ============================================================
        // 7. DATA TABEL (5 Terakhir dari SEMUA Satker)
        // ============================================================
        $pengajuanTerakhir = Pengajuan::whereYear('created_at', $tahunAktif)
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();

        return view('admin.dashboard', compact(
            'volumeGaji', 'volumePPNPN', 'volumeSKPP',
            'statusMenunggu', 'statusDiproses', 'statusSelesai', 'statusDitolak',
            'gajiPNS', 'gajiPPPK', 'gajiPOLRI', 'gajiTNI',
            'ppnpnBaru', 'ppnpnBatal',
            'skppPNS', 'skppPPPK', 'skppTNI', 'skppPOLRI',
            'pengajuanTerakhir',
            'trenPNS', 'trenPPPK', 'trenPOLRI', 'trenTNI',
            'tahunAktif' // Saya tambahkan ini agar bisa dipakai di Blade
        ));
    }
}