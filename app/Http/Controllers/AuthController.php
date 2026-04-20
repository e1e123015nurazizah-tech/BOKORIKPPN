<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter; 
use Illuminate\Support\Str; 

class AuthController extends Controller
{
    // Menampilkan halaman login Satker
    public function loginSatker()
    {
        return view('auth.login-satker');
    }

    // Proses login Satker (STRICT IP-BASED BLOCK)
    public function loginSatkerPost(Request $request)
    {
        // === TAMBAHAN VALIDASI RECAPTCHA SATKER ===
        $request->validate([
            'kode_satker' => 'required',
            'password' => 'required',
            'g-recaptcha-response' => 'required|captcha'
        ], [
            'g-recaptcha-response.required' => 'Wajib mencentang kotak "I\'m not a robot"!',
            'g-recaptcha-response.captcha'  => 'Verifikasi Captcha gagal/kadaluarsa. Silakan centang ulang.'
        ]);
        // ==========================================

        // 1. Kunci blokir hanya berdasarkan IP Perangkat
        $throttleKey = 'login_block|' . $request->ip();

        // 2. Cek blokir (Maksimal 5x percobaan salah)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'error' => "Akses perangkat diblokir sementara karena terlalu banyak percobaan gagal. Silakan coba lagi dalam {$minutes} menit."
            ])->withInput();
        }

        // 3. Proses Pengecekan Login
        if (Auth::guard('satker')->attempt(['kode_satker' => $request->kode_satker, 'password' => $request->password])) {
            // Jika berhasil, bersihkan riwayat kegagalan perangkat ini
            RateLimiter::clear($throttleKey);
            
            $request->session()->regenerate();
            return redirect()->route('dashboard.satker');
        }

        // 4. JIKA GAGAL: Catat kegagalan (Blokir 300 detik = 5 menit)
        RateLimiter::hit($throttleKey, 300);

        $sisaPercobaan = 5 - RateLimiter::attempts($throttleKey);
        $pesanError = ($sisaPercobaan > 0) 
            ? "Kode Satker atau Password salah! (Sisa percobaan perangkat: {$sisaPercobaan}x)" 
            : "Terlalu banyak kegagalan. Perangkat Anda diblokir selama 5 menit.";

        return back()->withErrors(['error' => $pesanError])->withInput();
    }

    // Menampilkan halaman login Admin
    public function loginAdmin()
    {
        return view('auth.login-admin');
    }

    // Proses login Admin (STRICT IP-BASED BLOCK)
    public function loginAdminPost(Request $request)
    {
        // === TAMBAHAN VALIDASI RECAPTCHA ADMIN ===
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
            'g-recaptcha-response' => 'required|captcha'
        ], [
            'g-recaptcha-response.required' => 'Wajib mencentang kotak "I\'m not a robot"!',
            'g-recaptcha-response.captcha'  => 'Verifikasi Captcha gagal/kadaluarsa. Silakan centang ulang.'
        ]);
        // =========================================

        // 1. Kunci blokir hanya berdasarkan IP Perangkat
        $throttleKey = 'login_block|' . $request->ip();

        // 2. Cek blokir (Maksimal 5x percobaan salah)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'error' => "Akses perangkat diblokir sementara karena terlalu banyak percobaan gagal. Silakan coba lagi dalam {$minutes} menit."
            ])->withInput();
        }

        // 3. Proses Pengecekan Login
        if (Auth::guard('admin')->attempt(['nip' => $request->nip, 'password' => $request->password])) {
            // Jika berhasil, bersihkan riwayat kegagalan perangkat ini
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // 4. JIKA GAGAL: Catat kegagalan (Blokir 300 detik = 5 menit)
        RateLimiter::hit($throttleKey, 300);

        $sisaPercobaan = 5 - RateLimiter::attempts($throttleKey);
        $pesanError = ($sisaPercobaan > 0) 
            ? "NIP atau Password salah! (Sisa percobaan perangkat: {$sisaPercobaan}x)" 
            : "Terlalu banyak kegagalan. Perangkat Anda diblokir selama 5 menit.";

        return back()->withErrors(['error' => $pesanError])->withInput();
    }

    // Logout
    public function logout(Request $request)
    {
        $tujuanRedirect = '/';
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $tujuanRedirect = '/admin';
        } elseif (Auth::guard('satker')->check()) {
            Auth::guard('satker')->logout();
            $tujuanRedirect = '/';
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect($tujuanRedirect);
    }
}