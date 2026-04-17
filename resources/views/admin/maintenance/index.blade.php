@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-[#1e3a8a]">Pembersihan Data & Storage</h2>
        <p class="text-gray-500 mt-2">Kelola kapasitas server dengan menghapus data lama atau berkas lampiran (PDF).</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-8 bg-red-50">
            <div class="flex items-center gap-4 text-red-700">
                <i class="fas fa-exclamation-triangle text-3xl"></i>
                <div>
                    <h3 class="font-bold text-lg">Zona Peringatan!</h3>
                    <p class="text-sm opacity-80">Aksi ini bersifat permanen. Pastikan Anda telah melakukan backup data jika diperlukan.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.maintenance.cleanup') }}" method="POST" class="p-8 space-y-8" id="formCleanup">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">1. Pilih Tahun Data</label>
                    <select name="tahun" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none transition-all font-semibold">
                        <option value="">-- Pilih Tahun --</option>
                        @php
                            // Mencari tahun paling lama dari tabel pengajuans, default ke tahun sekarang jika kosong
                            $tahunTerkecil = \App\Models\Pengajuan::min(\DB::raw('YEAR(created_at)')) ?: date('Y');
                        @endphp

                        @foreach(range($tahunTerkecil, date('Y')) as $thn)
                            <option value="{{ $thn }}">{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">2. Pilih Metode Penghapusan</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 transition-all group">
                            <input type="radio" name="mode" value="hanya_file" required class="w-5 h-5 text-blue-600">
                            <div class="ml-4">
                                <span class="block font-bold text-gray-800">Hanya Berkas PDF</span>
                                <span class="text-xs text-gray-500">Riwayat di tabel & statistik tetap ada, hanya file lampiran yang dihapus.</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-red-50 transition-all group">
                            <input type="radio" name="mode" value="semua" required class="w-5 h-5 text-red-600">
                            <div class="ml-4">
                                <span class="block font-bold text-red-700">Hapus Permanen (Semua)</span>
                                <span class="text-xs text-gray-500">Menghapus data di database beserta file lampirannya secara total.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-10 rounded-2xl shadow-lg shadow-red-200 transition-all transform hover:scale-105 flex items-center gap-3">
                    <i class="fas fa-eraser"></i>
                    Eksekusi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('formCleanup').onsubmit = function(e) {
        const tahun = this.tahun.value;
        const mode = this.querySelector('input[name="mode"]:checked').value;
        const modeText = mode === 'semua' ? 'MENGHAPUS TOTAL DATA DAN FILE' : 'MENGHAPUS HANYA FILE PDF';

        const konfirmasi1 = confirm(`Peringatan Akhir!\n\nAnda akan melakukan ${modeText} pada tahun ${tahun}.\n\nApakah Anda yakin?`);
        
        if (konfirmasi1) {
            const konfirmasi2 = confirm(`Sistem perlu memastikan sekali lagi.\n\nTindakan ini tidak bisa dibatalkan. Lanjutkan?`);
            return konfirmasi2;
        }
        
        return false;
    };
</script>
@endsection