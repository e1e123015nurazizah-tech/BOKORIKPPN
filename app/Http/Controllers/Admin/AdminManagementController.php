<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminsExport;
use App\Imports\AdminsImport;

class AdminManagementController extends Controller
{
    private function checkSuperAdmin()
    {
        if (Auth::guard('admin')->user()->role !== 'superadmin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus untuk Super Admin.');
        }
    }

    // 1. INDEX DENGAN FITUR PENCARIAN
    public function index(Request $request)
    {
        $this->checkSuperAdmin(); 
        $search = $request->input('search');

        $query = Admin::latest();

        if ($search) {
            $query->where('nip', 'LIKE', '%' . $search . '%')
                  ->orWhere('nama_lengkap', 'LIKE', '%' . $search . '%');
        }

        $admins = $query->paginate(10)->appends(['search' => $search]);
        return view('admin.kelola-admin.index', compact('admins', 'search'));
    }

    public function store(Request $request)
    {
        $this->checkSuperAdmin(); 
        
        $request->validate([
            'nip'          => 'required|string|max:25|unique:admins,nip',
            'nama_lengkap' => 'required|string|max:150',
            'jabatan'      => 'required|string|max:100',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:superadmin,operator,approver', 
        ], [
            'nip.unique'   => 'NIP ini sudah terdaftar sebagai Admin!',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        Admin::create([
            'nip'          => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan'      => $request->jabatan,
            'role'         => $request->role,
            'password'     => Hash::make($request->password),
        ]);

        return back()->with('success', 'Akun Administrator baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $this->checkSuperAdmin(); 
        
        if (Auth::guard('admin')->id() == $id) {
            return back()->withErrors(['Hapus Gagal' => 'Anda tidak bisa menghapus akun Anda sendiri!']);
        }

        $admin = Admin::findOrFail($id);
        
        if ($admin->foto_profil && \Storage::disk('public')->exists('profile_admin/' . $admin->foto_profil)) {
            \Storage::disk('public')->delete('profile_admin/' . $admin->foto_profil);
        }

        $admin->delete();

        return back()->with('success', 'Akun Administrator berhasil dihapus!');
    }

    public function resetPassword($id)
    {
        $this->checkSuperAdmin(); 
        $admin = Admin::findOrFail($id);
        $admin->password = Hash::make('bokori123');
        $admin->save();

        return back()->with('success', 'Password milik ' . $admin->nama_lengkap . ' berhasil di-reset menjadi: bokori123');
    }

    public function update(Request $request, $id)
    {
        $this->checkSuperAdmin(); 

        $admin = Admin::findOrFail($id);

        $request->validate([
            'nip'          => 'required|string|max:25|unique:admins,nip,' . $id,
            'nama_lengkap' => 'required|string|max:150',
            'jabatan'      => 'required|string|max:100',
            'role'         => 'required|in:superadmin,operator,approver',
        ], [
            'nip.unique'   => 'NIP ini sudah terdaftar sebagai Admin lain!',
        ]);

        if (Auth::guard('admin')->id() == $id && $request->role !== 'superadmin') {
             return back()->withErrors(['Ubah Gagal' => 'Anda tidak bisa menurunkan hak akses Anda sendiri!']);
        }

        $admin->update([
            'nip'          => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan'      => $request->jabatan,
            'role'         => $request->role,
        ]);

        return back()->with('success', 'Data Administrator berhasil diperbarui!');
    }

    // ========================================================
    // EXPORT & IMPORT EXCEL
    // ========================================================
    public function exportExcel()
    {
        $this->checkSuperAdmin();
        return Excel::download(new AdminsExport, 'Data_Admin_KPPN.xlsx');
    }

    public function importExcel(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            Excel::import(new AdminsImport, $request->file('file_excel'));
            return back()->with('success', 'Data Admin berhasil di-import dari Excel!');
        } catch (\Exception $e) {
            return back()->withErrors(['pesan' => 'Gagal meng-import file. Pastikan format kolom benar.']);
        }
    }
}