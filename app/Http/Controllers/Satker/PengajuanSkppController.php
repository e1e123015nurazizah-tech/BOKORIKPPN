<?php

namespace App\Http\Controllers\Satker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\DetailSkpp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon; // Tambahkan ini untuk pencatatan waktu yang presisi

class PengajuanSkppController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Inputan
        $request->validate([
            'nama_operator'    => 'required|string|max:150',
            'no_whatsapp'      => 'required|string|max:15',
            'anak_satker'      => 'required|string|max:150',
            'jenis_pegawai'    => 'required|in:PNS,TNI,POLRI,PPPK',
            'id_skpp'          => 'required|numeric',
            'jenis_skpp'       => 'required|string',
            'nomor_skpp'       => 'required|string|max:100',
            'nama_pegawai'     => 'required|string|max:150',
            'jumlah_pegawai'   => 'required|integer|min:1',
            'bulan_periode'    => 'required|string|max:50',
            'file_kelengkapan' => 'required|mimes:pdf|max:5120', // Memaksa format PDF & Maksimal ukuran 5 MB
        ]);

        DB::beginTransaction();

        try {
            // 2. Proses Simpan File PDF
            $file = $request->file('file_kelengkapan');

            // Buka file sementara dan baca 4 byte pertamanya
            $fileContent = fopen($file->getRealPath(), 'r');
            $magicBytes = fread($fileContent, 4);
            fclose($fileContent);

            // Jika 4 byte pertama bukan '%PDF', berarti ada indikasi pemalsuan ekstensi file
            if ($magicBytes !== '%PDF') {
                DB::rollBack(); // Batalkan transaksi database
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Peringatan Keamanan: File terindikasi palsu/corrupt. Pastikan file benar-benar berformat PDF asli.');
            }

            $namaFile = 'SKPP_' . date('YmdHis') . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            
            //'local' agar tersimpan di private storage
            $pathFile = $file->storeAs('berkas_skpp', $namaFile, 'local');

            // Ambil seluruh data Satker yang sedang login saat ini
            $satker = auth()->guard('satker')->user();

            // 3. Simpan ke Tabel Utama (Pengajuans)
            $pengajuan = Pengajuan::create([
                'nomor_tiket'      => 'SKP-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'nama_operator'    => $request->nama_operator,
                'no_whatsapp'      => $request->no_whatsapp,
                'kategori_layanan' => 'SKPP',
                'satker_id'        => $satker->id,

                // Mengambil ID Petugas langsung dari database Satker
                'admin_id'         => $satker->admin_skpp_id, 
                // Jika satker punya petugas khusus, langsung statusnya 'Diproses'. Jika tidak ada/kosong, 'Menunggu'
                'status'           => $satker->admin_skpp_id ? 'Diproses' : 'Menunggu',
                // Otomatis catat jam diambil jika langsung diserahkan ke petugas
                'waktu_diambil'    => $satker->admin_skpp_id ? Carbon::now() : null,
            ]);

            // 4. Simpan ke Tabel Rincian (Detail_Skpps)
            DetailSkpp::create([
                'pengajuan_id'     => $pengajuan->id,
                'anak_satker'      => $request->anak_satker,
                'jenis_pegawai'    => $request->jenis_pegawai,
                'id_skpp'          => $request->id_skpp,
                'jenis_skpp'       => $request->jenis_skpp,
                'nomor_skpp'       => $request->nomor_skpp,
                'nama_pegawai'     => $request->nama_pegawai,
                'jumlah_pegawai'   => $request->jumlah_pegawai,
                'bulan_periode'    => $request->bulan_periode,
                'file_kelengkapan' => $pathFile,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Berhasil! Pengajuan SKPP Anda telah terkirim dengan Nomor Tiket: ' . $pengajuan->nomor_tiket);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim pengajuan. Error: ' . $e->getMessage());
        }
    }
}