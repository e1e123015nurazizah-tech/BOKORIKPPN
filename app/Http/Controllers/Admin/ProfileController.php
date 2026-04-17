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

        // Validasi inputan form
        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'jabatan'      => 'required|string|max:100',
            'foto_profil'  => 'nullable|image|mimes:jpg,jpeg,png|max:5120', 
            'password_lama' => 'nullable|string',
            'password_baru' => [
                'nullable',
                'confirmed',
                // Custom Closure untuk Validasi Password Kuat (Bahasa Indonesia)
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
            // Pesan Error Kustom Bahasa Indonesia
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'foto_profil.image' => 'File harus berupa gambar (JPG, JPEG, PNG).',
            'foto_profil.max' => 'Ukuran foto maksimal adalah 5MB.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok, silakan ketik ulang.',
        ]);

        // 1. Update Data Teks
        $admin->nama_lengkap = $request->nama_lengkap;
        $admin->jabatan = $request->jabatan;

        // 2. Logika Upload Foto Profil (SUDAH DISAMAKAN DENGAN PENGAJUAN)
        if ($request->hasFile('foto_profil')) {
            
            // Hapus foto lama jika ada di disk public (langsung tembak path dari database)
            if ($admin->foto_profil && Storage::disk('public')->exists($admin->foto_profil)) {
                Storage::disk('public')->delete($admin->foto_profil);
            }

            // Generate nama file
            $namaFile = time() . '_' . $admin->nip . '.' . $request->foto_profil->extension();
            
            // SIMPAN KE DISK PUBLIC DAN AMBIL PATH LENGKAPNYA
            $pathLengkap = $request->foto_profil->storeAs('profile_admin', $namaFile, 'public');
            
            // SIMPAN PATH LENGKAP KE DATABASE (contoh: "profile_admin/12345.jpg")
            $admin->foto_profil = $pathLengkap;
        }

        // 3. Logika Update Password yang Ekstra Aman
        if ($request->filled('password_lama') || $request->filled('password_baru')) {
            
            // Cek apakah user mengisi keduanya
            if (!$request->filled('password_lama') || !$request->filled('password_baru')) {
                return back()->withErrors(['password' => 'Untuk mengganti password, Anda wajib mengisi Password Lama dan Password Baru.']);
            }

            // Cek apakah password lama sesuai dengan database
            if (!Hash::check($request->password_lama, $admin->password)) {
                return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah!']);
            }
            
            // Enkripsi dan simpan
            $admin->password = Hash::make($request->password_baru);
        }

        $admin->save();
        return back()->with('success', 'Profil Administrator berhasil diperbarui!');
    }

    /**
     * Menghapus Foto Profil (Database & File Fisik)
     */
    public function hapusFoto()
    {
        $admin = Auth::guard('admin')->user();

        // Pastikan admin benar-benar punya foto sebelum dihapus
        if ($admin->foto_profil) {
            
            // 1. HAPUS FILE FISIK DARI FOLDER STORAGE
            // Karena di database sekarang tersimpan path lengkap, kita tidak perlu nambah "profile_admin/" manual lagi!
            if (Storage::disk('public')->exists($admin->foto_profil)) {
                Storage::disk('public')->delete($admin->foto_profil);
            }

            // 2. HAPUS NAMA FILE DARI DATABASE
            $admin->update([
                'foto_profil' => null
            ]);

            return back()->with('success', 'Foto profil berhasil dihapus permanen!');
        }

        return back()->with('error', 'Anda belum memiliki foto profil.');
    }
}