<?php

namespace App\Http\Controllers\Satker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\DetailPpnpn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengajuanPpnpnController extends Controller
{
    public function store(Request $request)
    {
        // 1. JARING PENGAMAN: Validasi isian dari Satker
        $request->validate([
            'nama_operator'    => 'required|string|max:150',
            'no_whatsapp'      => 'required|string|max:15',
            'kode_anak_satker' => 'required|string|max:50',
            'jenis_adk'        => 'required|string',
            'id_adk'           => 'required|numeric', // Validasi backend: WAJIB ANGKA
            'tanggal_antrean'  => 'required|date',
            'bulan_periode'    => 'required|string|max:50',
        ]);

        // 2. MULAI TRANSAKSI DATABASE
        DB::beginTransaction();

        try {
            // 3. SIMPAN KE TABEL UTAMA (pengajuans)
            $pengajuan = Pengajuan::create([
                // Tiket untuk PPNPN kita beri kode awalan "PN-"
                'nomor_tiket'      => 'PN-' . date('Ymd') . '-' . strtoupper(Str::random(5)), 
                'nama_operator'    => $request->nama_operator,
                'no_whatsapp'      => $request->no_whatsapp,
                'kategori_layanan' => 'PPNPN',
                'status'           => 'Menunggu',
                'satker_id'        => auth()->id(), 
            ]);

            // 4. SIMPAN KE TABEL RINCIAN (detail_ppnpns)
            DetailPpnpn::create([
                'pengajuan_id'     => $pengajuan->id,
                'kode_anak_satker' => $request->kode_anak_satker,
                'jenis_adk'        => $request->jenis_adk,
                'id_adk'           => $request->id_adk,
                'tanggal_antrean'  => $request->tanggal_antrean,
                'bulan_periode'    => $request->bulan_periode,
            ]);

            // 5. BERHASIL! Simpan data secara permanen
            DB::commit();

            return redirect()->back()->with('success', 'Berhasil! Pengajuan PPNPN Anda telah terkirim dengan Nomor Tiket: ' . $pengajuan->nomor_tiket);

        } catch (\Exception $e) {
            // JIKA ERROR
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim pengajuan. Error: ' . $e->getMessage());
        }
    }
}