<?php

namespace App\Http\Controllers\Satker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard Utama Satker (Overview)
     */
    public function satkerIndex()
    {
        // 1. Ambil data Satker yang login
        $satker = Auth::guard('satker')->user();
        
        // ---  1. TANGKAP MEMORI TAHUN ---
        $tahunAktif = session('tahun_aktif', date('Y'));

        // ============================================================
        // 2. HITUNG VOLUME UTAMA (3 KOTAK BESAR)
        // ============================================================
        $volumeGaji = Pengajuan::where('satker_id', $satker->id)
                        ->where('kategori_layanan', 'GajiWeb')
                        ->whereYear('created_at', $tahunAktif)->count(); // <-- GEMBOK TAHUN

        $volumePPNPN = Pengajuan::where('satker_id', $satker->id)
                        ->where('kategori_layanan', 'PPNPN')
                        ->whereYear('created_at', $tahunAktif)->count(); // <-- GEMBOK TAHUN

        $volumeSKPP = Pengajuan::where('satker_id', $satker->id)
                        ->where('kategori_layanan', 'SKPP')
                        ->whereYear('created_at', $tahunAktif)->count(); // <-- GEMBOK TAHUN

        // ============================================================
        // 3. HITUNG STATUS (UNTUK DONUT CHART & CARDS)
        // ============================================================
        $statusMenunggu = Pengajuan::where('satker_id', $satker->id)->where('status', 'Menunggu')->whereYear('created_at', $tahunAktif)->count();
        $statusDiproses = Pengajuan::where('satker_id', $satker->id)->where('status', 'Diproses')->whereYear('created_at', $tahunAktif)->count();
        $statusSelesai  = Pengajuan::where('satker_id', $satker->id)->where('status', 'Selesai')->whereYear('created_at', $tahunAktif)->count();
        $statusDitolak  = Pengajuan::where('satker_id', $satker->id)->where('status', 'Ditolak')->whereYear('created_at', $tahunAktif)->count();

        // ============================================================
        // 4. DATA GRAFIK REKON GAJI
        // ============================================================
        $gajiPNS = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'GajiWeb')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'PNS'); })->count();
        $gajiPPPK = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'GajiWeb')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'PPPK'); })->count();
        $gajiPOLRI = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'GajiWeb')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'POLRI'); })->count();
        $gajiTNI = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'GajiWeb')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailGaji', function($q){ $q->where('jenis_pegawai', 'TNI'); })->count();

        // ============================================================
        // 5. DATA GRAFIK PPNPN
        // ============================================================
        $ppnpnBaru = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'PPNPN')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailPpnpn', function($q){ $q->where('jenis_adk', 'Pengajuan Baru'); })->count();
        $ppnpnBatal = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'PPNPN')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailPpnpn', function($q){ $q->where('jenis_adk', 'Pembatalan/Penghapusan'); })->count();

        // ============================================================
        // 6. DATA GRAFIK SKPP
        // ============================================================
        $skppPNS = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'SKPP')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'PNS'); })->count();
        $skppPPPK = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'SKPP')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'PPPK'); })->count();
        $skppTNI = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'SKPP')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'TNI'); })->count();
        $skppPOLRI = Pengajuan::where('satker_id', $satker->id)->where('kategori_layanan', 'SKPP')->whereYear('created_at', $tahunAktif)
                    ->whereHas('detailSkpp', function($q){ $q->where('jenis_pegawai', 'POLRI'); })->count();

        // ============================================================
        // 7. AKTIVITAS BULANAN (Multi-Line: PNS, PPPK, POLRI, TNI)
        // ============================================================
        $trenPNS = array_fill(0, 12, 0);
        $trenPPPK = array_fill(0, 12, 0);
        $trenPOLRI = array_fill(0, 12, 0);
        $trenTNI = array_fill(0, 12, 0);

        $pengajuansTren = Pengajuan::with(['detailGaji', 'detailSkpp'])
            ->where('satker_id', $satker->id)
            ->whereYear('created_at', $tahunAktif) // <-- GEMBOK TAHUN
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

            if ($jenisPegawai == 'PNS') { $trenPNS[$bulanIndex]++; } 
            elseif ($jenisPegawai == 'PPPK') { $trenPPPK[$bulanIndex]++; } 
            elseif ($jenisPegawai == 'POLRI') { $trenPOLRI[$bulanIndex]++; } 
            elseif ($jenisPegawai == 'TNI') { $trenTNI[$bulanIndex]++; }
        }

        return view('satker.dashboard', compact(
            'volumeGaji', 'volumePPNPN', 'volumeSKPP',
            'statusMenunggu', 'statusDiproses', 'statusSelesai', 'statusDitolak',
            'gajiPNS', 'gajiPPPK', 'gajiPOLRI', 'gajiTNI',
            'ppnpnBaru', 'ppnpnBatal',
            'skppPNS', 'skppPPPK', 'skppTNI', 'skppPOLRI',
            'trenPNS', 'trenPPPK', 'trenPOLRI', 'trenTNI',
            'tahunAktif' // Lempar ini ke view untuk mengubah teks judul
        ));
    }

    // ============================================================
    // 9. FUNGSI UNTUK MEMBUKA FORM PENGAJUAN
    // ============================================================

    public function createGajiweb() 
    {
        // Panggil tahun aktif agar tidak error saat dipanggil di Blade
        $tahunAktif = session('tahun_aktif', date('Y'));
        
        return view('satker.pengajuan.gajiweb', compact('tahunAktif'));
    }

    public function createPpnpn() 
    {
        $tahunAktif = session('tahun_aktif', date('Y'));
        
        return view('satker.pengajuan.ppnpn', compact('tahunAktif'));
    }

    public function createSkpp() 
    {
        $tahunAktif = session('tahun_aktif', date('Y'));
        
        return view('satker.pengajuan.skpp', compact('tahunAktif'));
    }

    // ============================================================
    // 10. FUNGSI UNTUK MONITORING (RIWAYAT PENGAJUAN)
    // ============================================================

    public function monitoringGajiweb(Request $request)
    {
        // --- TANGKAP MEMORI TAHUN ---
        $tahunAktif = session('tahun_aktif', date('Y'));

        $query = Pengajuan::with(['detailGaji', 'admin']) 
                          ->where('satker_id', Auth::guard('satker')->id())
                          ->where('kategori_layanan', 'GajiWeb')
                          ->whereYear('created_at', $tahunAktif); // <-- GEMBOK TAHUN

        // Filter Jenis Pegawai
        if ($request->filled('jenis_pegawai')) {
            $query->whereHas('detailGaji', function ($q) use ($request) {
                $q->where('jenis_pegawai', $request->jenis_pegawai);
            });
        }   
        if ($request->filled('bulan')) {
            $tigaHuruf = substr(trim($request->bulan), 0, 3);
            $query->whereHas('detailGaji', function ($q) use ($tigaHuruf) {
                $q->where('bulan_periode', 'LIKE', '%' . $tigaHuruf . '%');
            });
        }
        if ($request->filled('kategori_adk')) {
            $query->whereHas('detailGaji', function ($q) use ($request) {
                $q->where('kategori_adk', $request->kategori_adk);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(10);
        // Lempar tahunAktif ke view
        return view('satker.monitoring.gajiweb', compact('data', 'tahunAktif'));
    }

    public function monitoringPpnpn(Request $request)
    {
        // --- TANGKAP MEMORI TAHUN ---
        $tahunAktif = session('tahun_aktif', date('Y'));

        $query = Pengajuan::with(['detailPpnpn', 'admin']) 
                        ->where('satker_id', Auth::guard('satker')->id())
                        ->where('kategori_layanan', 'PPNPN')
                        ->whereYear('created_at', $tahunAktif); // <-- GEMBOK TAHUN

        // Filter 1: Jenis ADK
        if ($request->filled('jenis_adk')) {
            $query->whereHas('detailPpnpn', function ($q) use ($request) {
                $q->where('jenis_adk', $request->jenis_adk);
            });
        }

        // Filter 2: Bulan Periode (Pencarian Parsial)
        if ($request->filled('bulan')) {
            $query->whereHas('detailPpnpn', function ($q) use ($request) {
                $q->where('bulan_periode', 'LIKE', '%' . $request->bulan . '%');
            });
        }

        // Filter 3: ID ADK
        if ($request->filled('id_adk')) {
            $query->whereHas('detailPpnpn', function ($q) use ($request) {
                $q->where('id_adk', $request->id_adk);
            });
        }

        // Filter 4: Status Pengajuan
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(10);
        // Lempar tahunAktif ke view
        return view('satker.monitoring.ppnpn', compact('data', 'tahunAktif'));
    }

    public function monitoringSkpp(Request $request)
    {
        // --- TANGKAP MEMORI TAHUN ---
        $tahunAktif = session('tahun_aktif', date('Y'));

        $query = Pengajuan::with(['detailSkpp', 'admin']) 
                        ->where('satker_id', Auth::guard('satker')->id())
                        ->where('kategori_layanan', 'SKPP')
                        ->whereYear('created_at', $tahunAktif); // <-- GEMBOK TAHUN

        // 1. Filter Bulan Periode
        if ($request->filled('bulan')) {
            $query->whereHas('detailSkpp', function ($q) use ($request) {
                $q->where('bulan_periode', 'LIKE', '%' . $request->bulan . '%');
            });
        }

        // 2. Filter Jenis Pegawai
        if ($request->filled('jenis_pegawai')) {
            $query->whereHas('detailSkpp', function ($q) use ($request) {
                $q->where('jenis_pegawai', $request->jenis_pegawai);
            });
        }

        // 3. Filter Jenis SKPP
        if ($request->filled('jenis_skpp')) {
            $query->whereHas('detailSkpp', function ($q) use ($request) {
                $q->where('jenis_skpp', $request->jenis_skpp);
            });
        }

        // 4. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(10);
        // Lempar tahunAktif ke view
        return view('satker.monitoring.skpp', compact('data', 'tahunAktif'));
    }
}