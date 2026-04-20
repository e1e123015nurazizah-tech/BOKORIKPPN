<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Satker;
use App\Models\Admin; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SatkersExport;
use App\Imports\SatkersImport;

class SatkerController extends Controller
{
    // 1. Tampil Daftar Satker (Dengan Fitur Pencarian)
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query dasar
        $query = Satker::with('petugasSkpp')->latest();

        // Jika ada inputan pencarian
        if ($search) {
            $query->where('kode_satker', 'LIKE', '%' . $search . '%')
                  ->orWhere('nama_satker', 'LIKE', '%' . $search . '%');
        }

        // Paginasi dengan mempertahankan input pencarian di URL
        $data = $query->paginate(10)->appends(['search' => $search]);
        $admins = Admin::all(); 

        return view('admin.kelola-satker.index', compact('data', 'admins', 'search'));
    }

    // 2. Simpan Satker Baru (Manual)
    public function store(Request $request)
    {
        $request->validate([
            'kode_satker'   => 'required|string|max:6|unique:satkers,kode_satker',
            'nama_satker'   => 'required|string|max:150',
            'password'      => 'required|min:6',
            'admin_skpp_id' => 'nullable|exists:admins,id', 
        ], [
            'kode_satker.unique' => 'Kode Satker ini sudah terdaftar!',
        ]);

        $satkerData = [
            'kode_satker' => $request->kode_satker,
            'nama_satker' => $request->nama_satker,
            'password'    => Hash::make($request->password),
        ];

        if (Auth::guard('admin')->user()->role === 'superadmin') {
            $satkerData['admin_skpp_id'] = $request->admin_skpp_id;
        }

        Satker::create($satkerData);

        return back()->with('success', 'Satuan Kerja berhasil didaftarkan!');
    }

    // 3. Mengupdate Data Satker
    public function update(Request $request, $id)
    {
        $satker = Satker::findOrFail($id);

        $request->validate([
            'kode_satker'   => 'required|string|max:6|unique:satkers,kode_satker,' . $id,
            'nama_satker'   => 'required|string|max:150',
            'admin_skpp_id' => 'nullable|exists:admins,id', 
        ], [
            'kode_satker.unique' => 'Kode Satker ini sudah digunakan oleh Satker lain!',
        ]);

        $updateData = [
            'kode_satker' => $request->kode_satker,
            'nama_satker' => $request->nama_satker,
        ];

        if (Auth::guard('admin')->user()->role === 'superadmin') {
            $updateData['admin_skpp_id'] = $request->admin_skpp_id;
        }

        $satker->update($updateData);

        return back()->with('success', 'Data Satuan Kerja berhasil diperbarui!');
    }

    // 4. Reset Password Satker (Ke default: satker123)
    public function resetPassword($id)
    {
        $satker = Satker::findOrFail($id);
        $satker->update(['password' => Hash::make('satker123')]);

        return back()->with('success', 'Password ' . $satker->nama_satker . ' di-reset ke: satker123');
    }

    // 5. Hapus Satker
    public function destroy($id)
    {
        $satker = Satker::findOrFail($id);
        $satker->delete();

        return back()->with('success', 'Data Satker berhasil dihapus!');
    }

    // ========================================================
    // 6. IMPORT EXCEL MASSAL & AUTO ASSIGN
    // ========================================================
    public function importExcel(Request $request)
    {
        if (Auth::guard('admin')->user()->role !== 'superadmin') {
            return back()->withErrors(['pesan' => 'Akses Ditolak!']);
        }

        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            // 1. Panggil class Import-nya ke dalam variabel
            $import = new SatkersImport();
            
            // 2. Jalankan proses import
            Excel::import($import, $request->file('file_excel'));

            // 3. Rakit pesan dinamis berdasarkan hasil hitungan dari file Import
            $pesan = "Import Selesai! Sebanyak {$import->berhasil} Satker berhasil didaftarkan.";
            
            if ($import->gagal > 0) {
                $pesan .= " Namun, terdapat {$import->gagal} baris yang terlewat (karena Kode Satker sudah terdaftar atau datanya kosong).";
            }

            return back()->with('success', $pesan);

        } catch (\Exception $e) {
            return back()->withErrors(['pesan' => 'Gagal meng-import file. Pastikan format judul kolom di Excel sudah benar. Error: ' . $e->getMessage()]);
        }
    }

    // ========================================================
    // 7. EXPORT DATA KE EXCEL
    // ========================================================
    public function exportExcel()
    {
        return Excel::download(new SatkersExport, 'Data_Satuan_Kerja.xlsx');
    }
}