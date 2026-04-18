<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.index');
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // LOGIKA VALIDASI ASLI (Tidak diubah)
        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'jabatan'      => 'required|string|max:100',
            'foto_profil'  => 'nullable|image|mimes:jpg,jpeg,png|max:5120', 
            'password_lama' => 'nullable|string',
            'password_baru' => [
                'nullable',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) < 8) {
                        $fail('Sandi baru harus memiliki minimal 8 karakter.');
                    }
                    if (!preg_match('/[a-z]/', $value) || !preg_match('/[A-Z]/', $value)) {
                        $fail('Sandi baru harus menggunakan kombinasi huruf besar (A-Z) dan kecil (a-z).');
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('Sandi baru harus mengandung setidaknya satu angka (0-9).');
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $fail('Sandi baru harus mengandung setidaknya satu simbol khusus (@, #, $, dll).');
                    }
                },
            ],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'foto_profil.image' => 'File harus berupa gambar (JPG, JPEG, PNG).',
            'foto_profil.max' => 'Ukuran foto maksimal adalah 5MB.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok, silakan ketik ulang.',
        ]);

        // 1. Update Data Teks (Tidak diubah)
        $admin->nama_lengkap = $request->nama_lengkap;
        $admin->jabatan = $request->jabatan;

        // 2. LOGIKA UPLOAD FOTO PROFIL (Sudah Diperbaiki Agar Sejajar)
        if ($request->hasFile('foto_profil')) {
            
            // Hapus foto lama di disk LOCAL jika ada
            if ($admin->foto_profil && Storage::disk('local')->exists($admin->foto_profil)) {
                Storage::disk('local')->delete($admin->foto_profil);
            }

            // Generate nama file
            $namaFile = time() . '_' . $admin->nip . '.' . $request->foto_profil->extension();
            
            // SIMPAN KE FOLDER profile_admin
            // Karena disk('local') sudah otomatis mengarah ke folder 'storage/app/private',
            // maka hasilnya akan sejajar dengan berkas_gajiweb dan berkas_skpp.
            $pathLengkap = $request->foto_profil->storeAs('profile_admin', $namaFile, 'local');
            
            // Simpan path ke database (Akan tersimpan: "profile_admin/namafile.jpg")
            $admin->foto_profil = $pathLengkap;
        }

        // 3. Logika Update Password (Tidak diubah)
        if ($request->filled('password_lama') || $request->filled('password_baru')) {
            if (!$request->filled('password_lama') || !$request->filled('password_baru')) {
                return back()->withErrors(['password' => 'Untuk mengganti password, Anda wajib mengisi Password Lama dan Password Baru.']);
            }
            if (!Hash::check($request->password_lama, $admin->password)) {
                return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah!']);
            }
            $admin->password = Hash::make($request->password_baru);
        }

        $admin->save();
        return back()->with('success', 'Profil Administrator berhasil diperbarui!');
    }

    /**
     * Menghapus Foto Profil (Database & File Fisik Lokal)
     */
    public function hapusFoto()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->foto_profil) {
            
            // HAPUS DARI DISK LOCAL secara langsung dari path database
            if (Storage::disk('local')->exists($admin->foto_profil)) {
                Storage::disk('local')->delete($admin->foto_profil);
            }

            $admin->update(['foto_profil' => null]);

            return back()->with('success', 'Foto profil berhasil dihapus permanen!');
        }

        return back()->with('error', 'Anda belum memiliki foto profil.');
    }

    /**
     * Menampilkan Foto Profil Secara Aman (Pintu Akses Private)
     */
    public function showFoto($filename)
    {
        // Pastikan hanya admin yang login yang bisa buka foto ini
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Akses ditolak.');
        }

        // Arahkan ke folder private/profile_admin
        $path = storage_path('app/private/profile_admin/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Foto tidak ditemukan.');
        }

        // Response file otomatis dibaca sebagai gambar oleh browser
        return response()->file($path);
    }
}