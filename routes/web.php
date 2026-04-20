<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Satker\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Satker\PengajuanGajiController;
use App\Http\Controllers\Satker\PengajuanPpnpnController;
use App\Http\Controllers\Satker\PengajuanSkppController;


// ==========================================
// 1. JALUR LOGIN (Akses Publik / Tanpa Perlu Login)
// ==========================================
// Menampilkan halaman form login utama khusus untuk akun pengguna Satker
Route::get('/', [AuthController::class, 'loginSatker'])->name('login');
// Memproses pencocokan kode_satker dan password saat Satker menekan tombol login
Route::post('/login', [AuthController::class, 'loginSatkerPost'])->name('login.post');

// Menampilkan halaman form login rahasia khusus untuk Admin / Superadmin
Route::get('/admin', [AuthController::class, 'loginAdmin'])->name('admin.login');
// Memproses pencocokan NIP dan password saat Admin menekan tombol login
Route::post('/admin/login', [AuthController::class, 'loginAdminPost'])->name('admin.login.post');

// Mengubah tahun aktif pada sistem (menyimpan tahun ke dalam session browser)
Route::get('/set-tahun/{tahun}', function($tahun) {
    session(['tahun_aktif' => $tahun]);
    return redirect()->back();
})->name('set.tahun');


// ==========================================
// 2. JALUR SATKER (Kawasan Terlindungi - Wajib Login sebagai Satker)
// ==========================================
Route::middleware('auth:satker')->group(function () {
    
    // Menampilkan halaman beranda (Dashboard) meja kerja Satker
    Route::get('/dashboard-satker', [DashboardController::class, 'satkerIndex'])->name('dashboard.satker');

    // --- KELOMPOK MENU PEMBUATAN PENGAJUAN BARU ---
    Route::prefix('pengajuan')->group(function () {
        // Menampilkan form kosong untuk membuat pengajuan Gaji Web
        Route::get('/gajiweb', [DashboardController::class, 'createGajiweb'])->name('pengajuan.gajiweb');
        // Menampilkan form kosong untuk membuat pengajuan PPNPN
        Route::get('/ppnpn', [DashboardController::class, 'createPpnpn'])->name('pengajuan.ppnpn');
        // Menampilkan form kosong untuk membuat pengajuan SKPP
        Route::get('/skpp', [DashboardController::class, 'createSkpp'])->name('pengajuan.skpp');
        
        // Menyimpan data dan file PDF pengajuan Gaji Web ke dalam database
        Route::post('/gajiweb', [PengajuanGajiController::class, 'store'])->name('satker.gajiweb.store');
        // Menyimpan data dan file PDF pengajuan PPNPN ke dalam database
        Route::post('/ppnpn', [PengajuanPpnpnController::class, 'store'])->name('satker.ppnpn.store');
        // Menyimpan data dan file PDF pengajuan SKPP ke dalam database
        Route::post('/skpp', [PengajuanSkppController::class, 'store'])->name('satker.skpp.store');

        // Jalur darurat/cadangan jika proses simpan membutuhkan rute alternatif
        Route::post('/store', [DashboardController::class, 'storePengajuan'])->name('pengajuan.store');
    });

    // --- KELOMPOK MENU MONITORING (Melihat Riwayat Pengajuan) ---
    Route::prefix('monitoring')->group(function () {
        // Menampilkan tabel daftar riwayat pengajuan Gaji Web milik Satker tersebut
        Route::get('/gajiweb', [DashboardController::class, 'monitoringGajiweb'])->name('satker.monitoring.gajiweb');
        // Menampilkan tabel daftar riwayat pengajuan PPNPN milik Satker tersebut
        Route::get('/ppnpn', [DashboardController::class, 'monitoringPpnpn'])->name('satker.monitoring.ppnpn');
        // Menampilkan tabel daftar riwayat pengajuan SKPP milik Satker tersebut
        Route::get('/skpp', [DashboardController::class, 'monitoringSkpp'])->name('satker.monitoring.skpp');
    });

    // --- KELOMPOK MENU PROFIL SATKER ---
    // Menampilkan halaman profil Satker (ganti nama, ganti password)
    Route::get('/profil', [App\Http\Controllers\Satker\ProfileController::class, 'index'])->name('satker.profil');
    // Memproses penyimpanan perubahan data profil Satker ke database
    Route::post('/profil/update', [App\Http\Controllers\Satker\ProfileController::class, 'update'])->name('satker.profil.update');

});


// ==========================================
// 3. JALUR ADMIN (Kawasan Terlindungi - Wajib Login sebagai Admin/Superadmin)
// ==========================================
Route::middleware('auth:admin')->group(function () {
    
    // Menampilkan halaman beranda (Dashboard) meja kerja Administrator
    Route::get('/dashboard-admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // --- KELOMPOK MENU PROFIL ADMIN ---
    // Menampilkan halaman untuk mengatur nama, jabatan, foto, dan password Admin
    Route::get('/profil-admin', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('admin.profil');
    // Memproses penyimpanan data profil Admin yang baru diedit
    Route::post('/profil-admin', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profil.update');
    // Menghapus foto profil Admin secara permanen dari server dan database
    Route::delete('/profil-admin/hapus-foto', [App\Http\Controllers\Admin\ProfileController::class, 'hapusFoto'])->name('admin.profil.hapus_foto');
    // Jalur aman untuk menampilkan foto profil dari folder private
    Route::get('/lihat-profil/{filename}', [App\Http\Controllers\Admin\ProfileController::class, 'showFoto'])->name('profil.foto');

    // --- KELOMPOK MENU KELOLA ADMIN (Khusus Superadmin) ---
    // Menampilkan tabel daftar seluruh pegawai/Admin KPPN
    Route::get('/kelola-admin', [App\Http\Controllers\Admin\AdminManagementController::class, 'index'])->name('admin.kelola-admin.index');
    // Menyimpan pembuatan akun Admin baru ke database
    Route::post('/kelola-admin', [App\Http\Controllers\Admin\AdminManagementController::class, 'store'])->name('admin.kelola-admin.store');
    // Menyimpan perubahan data (edit) dari akun Admin yang sudah ada
    Route::put('/kelola-admin/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'update'])->name('admin.kelola-admin.update');
    // Menghapus akun Admin secara permanen
    Route::delete('/kelola-admin/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'destroy'])->name('admin.kelola-admin.destroy');
    // Mengembalikan password Admin ke pengaturan pabrik (admin123)
    Route::put('/kelola-admin/{id}/reset-password', [App\Http\Controllers\Admin\AdminManagementController::class, 'resetPassword'])->name('admin.kelola-admin.reset-password');
    // Mengekspor/mengunduh daftar Admin menjadi file Excel (.xlsx)
    Route::get('/kelola-admin/export', [App\Http\Controllers\Admin\AdminManagementController::class, 'exportExcel'])->name('admin.kelola-admin.export');
    // Import excel akun admin
    Route::post('/kelola-admin/import-excel', [App\Http\Controllers\Admin\AdminManagementController::class, 'importExcel'])->name('admin.kelola-admin.import');

    // --- KELOMPOK MENU KELOLA SATKER ---
    // Menampilkan tabel daftar seluruh akun Satker
    Route::get('/kelola-satker', [App\Http\Controllers\Admin\SatkerController::class, 'index'])->name('admin.satker.index');
    // Menyimpan pendaftaran akun Satker baru secara manual (satu per satu)
    Route::post('/kelola-satker', [App\Http\Controllers\Admin\SatkerController::class, 'store'])->name('admin.satker.store');
    // Mengekspor/mengunduh daftar Satker menjadi file Excel (.xlsx)
    Route::get('/kelola-satker/export', [App\Http\Controllers\Admin\SatkerController::class, 'exportExcel'])->name('admin.satker.export');
    // mport excel akun satker
    Route::post('/kelola-satker/import-excel', [App\Http\Controllers\Admin\SatkerController::class, 'importExcel'])->name('admin.satker.import');
    // Menyimpan perubahan data (edit nama/petugas) pada akun Satker tertentu
    Route::put('/kelola-satker/{id}', [App\Http\Controllers\Admin\SatkerController::class, 'update'])->name('admin.satker.update');
    // Mengembalikan password Satker ke pengaturan pabrik (satker123)
    Route::put('/kelola-satker/{id}/reset', [App\Http\Controllers\Admin\SatkerController::class, 'resetPassword'])->name('admin.satker.reset');
    // Menghapus akun Satker secara permanen dari sistem
    Route::delete('/kelola-satker/{id}', [App\Http\Controllers\Admin\SatkerController::class, 'destroy'])->name('admin.satker.destroy');
    
    // --- KELOMPOK MENU KELOLA DATA PENGAJUAN (Validasi Dokumen) ---
    // Menampilkan tabel daftar seluruh pengajuan yang masuk dari semua Satker
    Route::get('/pengajuan', [App\Http\Controllers\Admin\PengajuanController::class, 'index'])->name('admin.pengajuan.index');
    // Aksi ketika Admin menekan tombol "Ambil Tiket" (Status berubah menjadi 'Diproses')
    Route::put('/pengajuan/{id}/ambil', [App\Http\Controllers\Admin\PengajuanController::class, 'ambilTiket'])->name('admin.pengajuan.ambil');
    // Aksi ketika Admin menekan tombol "Selesai/Tolak" beserta pemberian catatannya
    Route::put('/pengajuan/{id}/proses', [App\Http\Controllers\Admin\PengajuanController::class, 'prosesTiket'])->name('admin.pengajuan.proses');
    // Aksi untuk membatalkan tiket yang sudah diambil agar bisa diambil Admin lain
    Route::put('/pengajuan/{id}/lepas', [App\Http\Controllers\Admin\PengajuanController::class, 'lepasTiket'])->name('admin.pengajuan.lepas');
    // Menghapus data pengajuan (biasanya untuk pengajuan yang salah input)
    Route::delete('/admin/pengajuan/{id}', [App\Http\Controllers\Admin\PengajuanController::class, 'destroy'])->name('admin.pengajuan.destroy');
    // Mengekspor/mengunduh seluruh data pengajuan (semua jenis) menjadi file Excel (.xlsx)
    Route::get('/pengajuan/export-semua', [App\Http\Controllers\Admin\PengajuanController::class, 'exportExcelSemua'])->name('admin.pengajuan.export_semua');
    // Mengekspor/mengunduh data pengajuan khusus SKPP untuk keperluan approver menjadi file Excel (.xlsx)
    Route::get('/pengajuan/export-skpp-approver', [App\Http\Controllers\Admin\PengajuanController::class, 'exportExcelApprover'])->name('admin.pengajuan.export_approver');

    // --- KELOMPOK MENU MAINTENANCE (HANYA SUPERADMIN) ---
    // (Aman: Sudah dimasukkan ke dalam pelindung middleware admin!)
    // Menampilkan halaman panel kontrol untuk membersihkan (cleanup) data lawas
    Route::get('/maintenance', [App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('admin.maintenance.index');
    // Mengeksekusi penghapusan file PDF atau penghapusan permanen data pengajuan berdasarkan tahun
    Route::post('/maintenance/cleanup', [App\Http\Controllers\Admin\MaintenanceController::class, 'cleanup'])->name('admin.maintenance.cleanup');
});

// ==========================================
// JALUR AKSES FILE AMAN (Proxy Route)
// ==========================================
// Rute ini digabung agar Satker maupun Admin bisa mengakses dengan satu jalur yang sama
Route::middleware(['auth:satker,admin'])->group(function () {
        Route::get('/lihat-dokumen/{kategori}/{id}/{filename?}', [App\Http\Controllers\Admin\PengajuanController::class, 'viewPdf'])
            ->name('dokumen.view');
});


// ==========================================
// 4. JALUR LOGOUT (Keluar dari Sistem)
// ==========================================
// Menghapus sesi aktif pengguna (baik Satker maupun Admin) dan mengembalikannya ke halaman login
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');