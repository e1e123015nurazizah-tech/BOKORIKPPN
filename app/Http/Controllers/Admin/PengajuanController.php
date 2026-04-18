<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllPengajuanExport;
use Illuminate\Support\Facades\Storage;

class PengajuanController extends Controller
{
    /**
     * 1. Menampilkan Daftar Pengajuan (Dilengkapi Filter Canggih, Statistik & TAHUN GLOBAL)
     */
    public function index(Request $request)
    {
        $adminLogin = Auth::guard('admin')->user();
        
        // --- AMBIL MEMORI TAHUN DARI SESSION ---
        $tahunAktif = session('tahun_aktif', date('Y'));
        
        // --- TAMBAHKAN whereYear PADA INISIASI QUERY AWAL ---
        $query = Pengajuan::with(['satker', 'admin', 'detailGaji', 'detailPpnpn', 'detailSkpp'])
                          ->whereYear('created_at', $tahunAktif); // GEMBOK TAHUN
                          
        $statsQuery = Pengajuan::whereYear('created_at', $tahunAktif); // GEMBOK TAHUN STATISTIK

        // --- GEMBOK FILTER KHUSUS ROLE APPROVER ---
        if ($adminLogin->role === 'approver') {
            // Tabel Bawah (Semua Data): Approver bisa melihat SEMUA RIWAYAT SKPP
            $query->where('kategori_layanan', 'SKPP');
            
            // Statistik: Hanya hitung dari kategori SKPP
            $statsQuery->where('kategori_layanan', 'SKPP');
        } else {
            // Filter Kategori normal untuk Operator/Superadmin
            if ($request->has('kategori') && $request->kategori != '') {
                $query->where('kategori_layanan', $request->kategori);
                $statsQuery->where('kategori_layanan', $request->kategori);
            }
        }

        // --- MENGHITUNG STATISTIK (OVERVIEW) ---
        // Karena $statsQuery di atas sudah digembok tahunnya, semua perhitungan di bawah otomatis mengikuti tahun yang dipilih
        $stats = [
            'menunggu'          => (clone $statsQuery)->where('status', 'Menunggu')->count(),
            'tugas_saya'        => (clone $statsQuery)->where('status', 'Diproses')->where('admin_id', $adminLogin->id)->count(),
            'menunggu_approval' => (clone $statsQuery)->where('status', 'Menunggu Approval')->count(),
            'selesai'           => (clone $statsQuery)->where('status', 'Selesai')->count(),
            'ditolak'           => (clone $statsQuery)->where('status', 'Ditolak')->count(),
        ];
        // ---------------------------------------

        // --- MEJA KERJA SAYA (Dinamis per Menu & Role) ---
        // --- TAMBAHKAN whereYear DI SINI JUGA ---
        $queryTugasAktif = Pengajuan::with(['satker', 'admin', 'detailGaji', 'detailPpnpn', 'detailSkpp'])
                                    ->whereYear('created_at', $tahunAktif); // GEMBOK TAHUN TUGAS AKTIF
        
        if ($adminLogin->role === 'approver') {
            // Meja kerja Approver hanya berisi SKPP yang Menunggu Approval
            $queryTugasAktif->where('status', 'Menunggu Approval')
                            ->where('kategori_layanan', 'SKPP');
        } else {
            // Meja kerja Operator berisi tiket yang sedang dia kerjakan
            $queryTugasAktif->where('admin_id', $adminLogin->id)
                            ->where('status', 'Diproses');
            
            if ($request->has('kategori') && $request->kategori != '') {
                $queryTugasAktif->where('kategori_layanan', $request->kategori);
            }
        }

        $tugasAktif = $queryTugasAktif->latest()->get();
        // ------------------------------------

        // --- FILTER PENCARIAN & STATUS (Hanya Aktif untuk Non-Approver) ---
        if ($adminLogin->role !== 'approver') {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_tiket', 'like', "%{$search}%")
                      ->orWhereHas('satker', function($q2) use ($search) {
                          $q2->where('nama_satker', 'like', "%{$search}%")
                             ->orWhere('kode_satker', 'like', "%{$search}%"); 
                      });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->petugas == 'saya') {
                $query->where('admin_id', $adminLogin->id);
            }
        }

        $pengajuans = $query->latest()->paginate(10);
        $kategori = $request->kategori;

        // JIKA OPERATOR / SUPERADMIN: Buka file sesuai klik di Sidebar
        if ($kategori === 'SKPP') {
            return view('admin.pengajuan.skpp', compact('pengajuans', 'stats', 'tugasAktif'));
        } elseif ($kategori === 'GajiWeb') {
            return view('admin.pengajuan.gajiweb', compact('pengajuans', 'stats', 'tugasAktif'));
        } elseif ($kategori === 'PPNPN') {
            return view('admin.pengajuan.ppnpn', compact('pengajuans', 'stats', 'tugasAktif'));
        } else {
            // Default jika tidak ada kategori ("Semua Data" -> index.blade.php)
            return view('admin.pengajuan.index', compact('pengajuans', 'stats', 'tugasAktif'));
        }
    }

    /**
     * 2. Logika Admin Mengambil/Booking Tiket
     */
    public function ambilTiket($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $adminLogin = Auth::guard('admin')->user();

        // 1. Mencegah Approver mengambil tiket antrean biasa
        if ($adminLogin->role === 'approver') {
            return back()->withErrors(['pesan' => 'Pimpinan/Approver tidak perlu mengambil tiket antrean awal.']);
        }

        // 2. PERTAHANAN LAPIS 1: Cek apakah status sudah bukan 'Menunggu' ATAU sudah ada admin_id
        // Ini untuk menangkap admin yang belum refresh halaman
        if ($pengajuan->status !== 'Menunggu' || !is_null($pengajuan->admin_id)) {
            return back()->withErrors(['pesan' => 'Maaf, tiket ini baru saja diambil atau sedang diproses oleh Admin lain (Harap refresh halaman).']);
        }

        // 3. Jika lolos, baru tandai sebagai milik admin yang klik
        $pengajuan->update([
            'admin_id'      => $adminLogin->id,
            'status'        => 'Diproses',
            'waktu_diambil' => Carbon::now(),
        ]);

        return back()->with('success', 'Tiket berhasil diambil! Silakan periksa berkas di meja kerja Anda.');
    }

    /**
     * 3. Logika Melepas Tiket (UNBOOK)
     */
    public function lepasTiket($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $adminLogin = Auth::guard('admin')->user();

        if ($pengajuan->admin_id !== $adminLogin->id && $adminLogin->role !== 'superadmin') {
            return back()->withErrors(['pesan' => 'Akses Ditolak! Anda tidak bisa melepas tiket yang sedang dikerjakan oleh Admin lain.']);
        }

        $pengajuan->update([
            'admin_id'      => null,
            'status'        => 'Menunggu',
            'waktu_diambil' => null,
        ]);

        return back()->with('success', 'Tiket berhasil dilepas dan dikembalikan ke antrean Menunggu!');
    }

    /**
     * 4. Logika Menyelesaikan, Menolak, atau MENGUBAH Status Pengajuan
     */
    public function prosesTiket(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $adminLogin = Auth::guard('admin')->user();

        // Admin Biasa dilarang masuk jika status sudah 'Menunggu Approval' atau 'Selesai'
        // Approver dan Superadmin tetap LOLOS (Bisa edit/revisi)
        if ($adminLogin->role === 'admin' && in_array($pengajuan->status, ['Menunggu Approval', 'Selesai'])) {
            return back()->withErrors(['pesan' => 'Akses Ditolak! Data ini sudah berada di meja pimpinan atau telah selesai.']);
        }

        // --- VALIDASI HAK AKSES ---
        if ($adminLogin->role === 'approver') {
            if ($pengajuan->status !== 'Menunggu Approval') {
                return back()->withErrors(['pesan' => 'Akses Ditolak! Anda hanya dapat memproses tiket yang sudah diperiksa oleh Operator (Menunggu Approval).']);
            }
        } else {
            if ($pengajuan->admin_id !== $adminLogin->id && $adminLogin->role !== 'superadmin') {
                return back()->withErrors(['pesan' => 'Akses Ditolak! Anda tidak memiliki hak untuk memproses atau mengubah tiket milik Admin lain.']);
            }
        }

        $request->validate([
            'status'  => 'required|in:Selesai,Ditolak,Menunggu Approval',
            'catatan' => 'nullable|string',
        ], [
            'status.required' => 'Status persetujuan wajib dipilih.',
            'status.in'       => 'Pilihan status tidak valid.',
        ]);

        $targetStatus = $request->status;

        // Jika tiket ini adalah SKPP, dan yang setuju BUKAN Approver, maka belokkan ke Menunggu Approval
        if ($targetStatus === 'Selesai' && $pengajuan->kategori_layanan === 'SKPP') {
            if ($adminLogin->role !== 'approver') {
                $targetStatus = 'Menunggu Approval'; 
            }
        }

        if ($targetStatus === 'Ditolak' && empty($request->catatan)) {
            return back()->withErrors(['catatan' => 'Jika pengajuan ditolak, Anda WAJIB memberikan catatan alasannya agar Satker bisa memperbaiki.']);
        }

        // Eksekusi Update ke Database
        $pengajuan->update([
            'status'  => $targetStatus,
            'catatan' => $request->catatan,
        ]);

        // Pesan sukses yang dinamis
        $pesan = ($targetStatus === 'Menunggu Approval') 
            ? 'Berkas telah diperiksa! Tiket SKPP berhasil diteruskan ke Pimpinan (Approver).' 
            : 'Keputusan berhasil disimpan! Status pengajuan saat ini: ' . $targetStatus;

        return back()->with('success', $pesan);
    }

    /**
     * 5. Logika Hapus Data Pengajuan Permanen
     */
    public function destroy($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $adminLogin = Auth::guard('admin')->user();

        // Admin Biasa dilarang hapus jika data sudah di pimpinan atau selesai
        // Superadmin tetap LOLOS (Bebas hapus kapan saja)
        if ($adminLogin->role === 'admin' && in_array($pengajuan->status, ['Menunggu Approval', 'Selesai'])) {
            return back()->withErrors(['pesan' => 'Gagal! Anda tidak bisa menghapus tiket yang sedang dalam proses approval atau telah selesai.']);
        }

        // VALIDASI HAK AKSES HAPUS
        if ($adminLogin->role === 'approver') {
            return back()->withErrors(['pesan' => 'Akses Ditolak! Approver tidak memiliki wewenang menghapus tiket.']);
        }

        // HANYA Superadmin atau Admin yang punya tiket tersebut yang boleh hapus
        // Jika tiket belum diambil (status Menunggu), Superadmin atau Admin manapun boleh hapus.
        if ($pengajuan->admin_id !== null && $pengajuan->admin_id !== $adminLogin->id && $adminLogin->role !== 'superadmin') {
            return back()->withErrors(['pesan' => 'Akses Ditolak! Anda hanya boleh menghapus tiket yang sedang Anda kerjakan atau tiket baru.']);
        }
        $pengajuan->delete();
        return back()->with('success', 'Berhasil! Data pengajuan telah dihapus secara permanen dari sistem.');
    }
    public function exportExcelSemua()
    {
        // Ambil tahun aktif dari session (pilihan pojok kanan), default ke tahun sekarang
        $tahunAktif = session('tahun_aktif', date('Y'));

        return Excel::download(new AllPengajuanExport($tahunAktif), "Rekap_Seluruh_Pengajuan_{$tahunAktif}.xlsx");
    }
    public function exportExcelApprover()
    {
        // Ambil tahun aktif dari session
        $tahunAktif = session('tahun_aktif', date('Y'));
        
        return Excel::download(new \App\Exports\ApproverSkppExport($tahunAktif), "Rekap_SKPP_{$tahunAktif}.xlsx");
    }
    /**
     * 6. Logika Akses File PDF Aman (Proxy Route)
     */
    public function viewPdf($kategori, $id, $filename = null)
    {
        // 1. Cari data pengajuan utama berdasarkan ID ($id sekarang adalah ID Pengajuan)
        $pengajuan = Pengajuan::findOrFail($id);
        $kategori = strtolower($kategori);

        // 2. Ambil detail file berdasarkan kategori
        if ($kategori === 'skpp') {
            $detail = $pengajuan->detailSkpp;
        } elseif ($kategori === 'gajiweb') {
            $detail = $pengajuan->detailGaji; 
        } elseif ($kategori === 'ppnpn') {
            $detail = $pengajuan->detailPpnpn;
        } else {
            abort(404, 'Kategori pengajuan tidak valid.');
        }

        // Jika rincian data tidak ada
        if (!$detail) {
            abort(404, 'Data rincian tidak ditemukan.');
        }

        // 3. VALIDASI HAK AKSES (Keamanan Lapis 2)
        if (Auth::guard('satker')->check()) {
            // Jika Satker: Hanya boleh melihat dokumen miliknya sendiri
            if ($pengajuan->satker_id !== Auth::guard('satker')->id()) {
                abort(403, 'Akses Ditolak! Anda tidak diizinkan melihat dokumen satker lain.');
            }
        } elseif (Auth::guard('admin')->check()) {
            $adminLogin = Auth::guard('admin')->user();
            
            // Jika Admin Biasa: Boleh lihat jika tiket belum diambil ATAU dia yang sedang kerjakan
            if ($adminLogin->role === 'admin' && $pengajuan->admin_id !== null && $pengajuan->admin_id !== $adminLogin->id) {
                abort(403, 'Akses Ditolak! Dokumen ini sedang ditangani oleh petugas lain.');
            }
        } else {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // 4. Cari lokasi fisik file secara otomatis (baik di 'local' maupun 'public')
        $path = $detail->file_kelengkapan;
        $realPath = null;

        if (Storage::disk('local')->exists($path)) {
            $realPath = Storage::disk('local')->path($path);
        } elseif (Storage::disk('public')->exists($path)) {
            $realPath = Storage::disk('public')->path($path);
        } else {
            abort(404, 'Maaf, berkas fisik PDF tidak ditemukan di server.');
        }

        // 5. Menentukan Nama File saat di-download/dibuka (Ditambah ID Pengajuan)
        // Hasilnya akan menjadi: Dokumen_SKPP_23.pdf
        $namaFileDownload = 'Dokumen_' . strtoupper($kategori) . '_' . $id . '.pdf';

        // 6. Tampilkan file PDF langsung di tab browser dengan nama yang sudah disesuaikan
        return response()->file($realPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $namaFileDownload . '"'
        ]);
    }
}