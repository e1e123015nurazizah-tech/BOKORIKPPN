<?php

namespace App\Http\Controllers\Satker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\DetailGajiweb;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengajuanGajiController extends Controller
{
    // Fungsi ini akan dijalankan saat Satker klik tombol "Kirim Pengajuan"
    public function store(Request $request)
    {
        // 1. JARING PENGAMAN: Validasi data agar form tidak diisi sembarangan
        $request->validate([
            'nama_operator'    => 'required|string|max:150',
            'no_whatsapp'      => 'required|string|max:15',
            'jenis_pegawai'    => 'required|string',
            'jenis_proses'     => 'required|string',
            'kategori_adk'     => 'required|string',
            'bulan_periode'    => 'required|string|max:50',
            'file_kelengkapan' => 'required|mimes:pdf|max:5120', // Memaksa format PDF & Maksimal ukuran 5 MB
            'catatan_satker'   => 'nullable|string',
        ]);

        // 2. MULAI TRANSAKSI DATABASE (Mencegah data tersimpan sebagian jika tiba-tiba error)
        DB::beginTransaction();

        try {
            // 3. PROSES UPLOAD FILE PDF KE SERVER
            $file = $request->file('file_kelengkapan');
            
            // Membuat nama file yang unik biar tidak tertukar (Contoh: GajiWeb_20260408153022_XYZ12.pdf)
            $namaFile = 'GajiWeb_' . date('YmdHis') . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            
            // Menyimpan file PDF ke dalam folder: public/storage/berkas_gajiweb
            $pathFile = $file->storeAs('berkas_gajiweb', $namaFile, 'public');

            // 4. SIMPAN DATA KE TABEL UTAMA (pengajuans)
            $pengajuan = Pengajuan::create([
                // Membuat Nomor Tiket otomatis untuk resi pelacakan
                'nomor_tiket'      => 'GW-' . date('Ymd') . '-' . strtoupper(Str::random(5)), 
                'nama_operator'    => $request->nama_operator,
                'no_whatsapp'      => $request->no_whatsapp,
                'kategori_layanan' => 'GajiWeb',
                'status'           => 'Menunggu',
                // Mengambil ID user Satker yang sedang login saat ini
                'satker_id'        => auth()->id(), 
            ]);

            // 5. SIMPAN DATA KE TABEL RINCIAN (detail_gajiwebs)
            DetailGajiweb::create([
                'pengajuan_id'     => $pengajuan->id, // Menyambungkan rincian ini dengan tiket di tabel utama
                'jenis_pegawai'    => $request->jenis_pegawai,
                'jenis_proses'     => $request->jenis_proses,
                'kategori_adk'     => $request->kategori_adk,
                'bulan_periode'    => $request->bulan_periode,
                'file_kelengkapan' => $pathFile, // Menyimpan jalur lokasi file PDF yang tadi di-upload
                'catatan_satker'   => $request->catatan_satker,
            ]);

            // 6. BERHASIL! Simpan data secara permanen ke database
            DB::commit();

            // Kembali ke halaman form sambil membawa pesan sukses
            return redirect()->back()->with('success', 'Berhasil! Pengajuan Gaji Web Anda telah terkirim dengan Nomor Tiket: ' . $pengajuan->nomor_tiket);

        } catch (\Exception $e) {
            // JIKA ADA ERROR (Misalnya hardisk server penuh), kembalikan database seperti semula
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim pengajuan. Error sistem: ' . $e->getMessage());
        }
    }
}