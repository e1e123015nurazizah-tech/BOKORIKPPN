<?php

namespace App\Http\Controllers\Satker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form profil Satker
     */
    public function index()
    {
        return view('satker.profile.index');
    }

    /**
     * Menyimpan perubahan nama atau password Satker
     */
    public function update(Request $request)
    {
        // Ambil data satker yang sedang login
        $satker = Auth::guard('satker')->user();

        // Validasi inputan dari form dengan Custom Closure 
        $request->validate([
            'nama_satker' => 'required|string|max:255',
            'password_lama' => 'nullable|string',
            'password_baru' => [
                'nullable',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Cek minimal 8 karakter
                    if (strlen($value) < 8) {
                        $fail('Sandi baru harus memiliki minimal 8 karakter.');
                    }
                    // Cek huruf besar dan kecil
                    if (!preg_match('/[a-z]/', $value) || !preg_match('/[A-Z]/', $value)) {
                        $fail('Sandi baru harus menggunakan kombinasi huruf besar (A-Z) dan kecil (a-z).');
                    }
                    // Cek angka
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('Sandi baru harus mengandung setidaknya satu angka (0-9).');
                    }
                    // Cek simbol
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $fail('Sandi baru harus mengandung setidaknya satu simbol khusus (@, #, $, dll).');
                    }
                },
            ], 
        ], [
            // Pesan error standar
            'nama_satker.required' => 'Nama Satuan Kerja wajib diisi.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok, silakan ketik ulang.',
        ]);

        // 1. Update Nama Satker (Selalu di-update)
        $satker->nama_satker = $request->nama_satker;

        // 2. Logika Update Password
        if ($request->filled('password_lama') || $request->filled('password_baru')) {
            
            // Wajib isi ketiganya kalau mau ganti password
            if (!$request->filled('password_lama') || !$request->filled('password_baru')) {
                return back()->withErrors(['password' => 'Untuk mengganti password, Anda wajib mengisi Password Lama dan Password Baru.']);
            }

            // Cek apakah password lama yang dimasukkan COCOK dengan yang ada di database
            if (!Hash::check($request->password_lama, $satker->password)) {
                return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah!']);
            }
            
            // Jika cocok, enkripsi dan simpan password baru
            $satker->password = Hash::make($request->password_baru);
        }

        // Simpan perubahan ke database
        $satker->save();

        // Kembalikan ke halaman profil dengan pesan sukses
        return back()->with('success', 'Profil dan pengaturan akun berhasil diperbarui!');
    }
}