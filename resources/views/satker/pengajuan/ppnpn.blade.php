@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-4xl font-black text-[#1e3a8a] tracking-tight">Form Rekon PPNPN</h2>
            <p class="text-gray-500 mt-2 text-lg italic">Lengkapi formulir di bawah ini sesuai dengan data PPNPN Anda.</p>
        </div>
        <a href="{{ route('dashboard.satker') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-8 p-5 bg-green-50 border-l-4 border-green-500 rounded-r-2xl shadow-sm flex items-start animate-bounce-short">
            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-4 text-2xl"></i>
            <div>
                <h3 class="text-green-800 font-black text-lg mb-1">Pengajuan Berhasil Terkirim!</h3>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-8 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-2xl shadow-sm flex items-start">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-4 text-2xl"></i>
            <div>
                <h3 class="text-red-800 font-black text-lg mb-1">Terjadi Kesalahan!</h3>
                <p class="text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="mb-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl shadow-sm">
        <h4 class="text-sm font-bold text-[#1e3a8a] mb-2">KPPN Kendari</h4>
        <ul class="text-[12px] text-blue-900 list-none space-y-1 font-medium">
            <li>- <span class="font-bold text-red-600">HARAP MENGISI KODE ANAK SATKER</span></li>
            <li>- Harap melakukan konfirmasi hanya satu kali</li>
            <li>- Konfirmasi ganda berakibat ADK tidak dapat diproses</li>
            <li>- Jenis ADK</li>
            <li class="pl-3">Pengajuan Baru, apabila satker mengajukan ADK PPNPN baru</li>
            <li class="pl-3">Pengajuan Pembatalan, apabila satker ingin mengajukan pembatalan/penghapusan ADK karena terdapat kesalahan yang ditemukan lebih awal</li>
            <li class="pt-2 font-bold italic">Hubungi CSO atau sampaikan keluhan pada grup whatsapp satker apabila mengalami kendala</li>
        </ul>
    </div>

    <div class="bg-white p-10 rounded-[35px] shadow-sm border border-gray-100" 
         x-data="{ jenisAdk: '', idAdk: '', isIdError: false, noWhatsapp: '', isWaError: false }">
        
        <form action="{{ route('satker.ppnpn.store') }}" method="POST">
            @csrf

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">1. Data Petugas (PIC)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Nama Operator <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_operator" placeholder="Nama lengkap petugas PIC" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="no_whatsapp" placeholder="Contoh: 081234567890 atau +62..." required 
                           x-model="noWhatsapp" 
                           @input="isWaError = noWhatsapp !== '' && !/^\+?\d*$/.test(noWhatsapp)"
                           :class="isWaError ? 'border-red-500 focus:ring-red-500 bg-red-50 text-red-600' : 'border-gray-200 focus:ring-blue-500 bg-gray-50 text-gray-700'"
                           class="w-full border py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 transition-all font-medium">
                    <p x-show="isWaError" x-cloak class="text-[11px] text-red-600 font-bold mt-2 animate-pulse">
                        <i class="fas fa-times-circle mr-1"></i> Nomor WhatsApp hanya boleh angka atau diawali +
                    </p>
                </div>
            </div>

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">2. Rincian ADK PPNPN</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Kode Anak Satker <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_anak_satker" placeholder="Masukkan Kode Anak Satker" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold">
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Jenis ADK <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="jenis_adk" x-model="jenisAdk" required 
                                class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                            <option value="" disabled selected>-- Pilih Jenis ADK --</option>
                            <option value="Pengajuan Baru">Pengajuan Baru</option>
                            <option value="Pembatalan/Penghapusan">Pembatalan / Penghapusan</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-6 text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">ID ADK <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic leading-tight">Isi dengan ID yang terdapat pada aplikasi GAJIKITA (Jumlahnya 5 digit), Contoh: 18290</p>
                    
                    <input type="text" name="id_adk" placeholder="18290" required 
                           x-model="idAdk" 
                           @input="isIdError = idAdk !== '' && !/^\d+$/.test(idAdk)"
                           :class="isIdError ? 'border-red-500 focus:ring-red-500 bg-red-50 text-red-600' : 'border-gray-200 focus:ring-blue-500 bg-gray-50 text-gray-700'"
                           class="w-full border py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 transition-all font-medium">
                    
                    <p x-show="isIdError" x-cloak class="text-[11px] text-red-600 font-bold mt-2 animate-pulse">
                        <i class="fas fa-times-circle mr-1"></i> ID ADK salah! Hanya boleh berisi angka.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Tanggal Antrean <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic leading-tight">Isi sesuai dengan Tanggal Antrean yang tertera pada GAJIKITA</p>
                    <input type="date" name="tanggal_antrean" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Bulan Periode <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic leading-tight">Format: NAMA BULAN + TAHUN (Wajib KAPITAL)<br>Contoh: JANUARI {{ $tahunAktif }}</p>
                    <input type="text" name="bulan_periode" placeholder="Contoh: JANUARI {{ $tahunAktif }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold uppercase placeholder:font-normal">
                </div>
            </div>
            
            @php 
                $tahunAsli = date('Y');
                $tahunPilihan = session('tahun_aktif', $tahunAsli);
            @endphp

            <div class="flex items-center justify-end pt-6 border-t border-gray-100 mt-8">
                @if($tahunPilihan == $tahunAsli)
                    {{-- JIKA TAHUN SAMA: Munculkan Tombol Kirim Pengajuan Aslimu --}}
                    <button type="submit" 
                            :disabled="isIdError || isWaError"
                            :class="(isIdError || isWaError) 
                                ? 'bg-gradient-to-r from-gray-400 to-gray-500 opacity-50 cursor-not-allowed shadow-none' 
                                : 'bg-gradient-to-r from-blue-600 to-[#1e3a8a] hover:shadow-lg hover:shadow-blue-500/40 hover:-translate-y-0.5 shadow-md shadow-blue-500/20'"
                            class="text-white px-8 py-3.5 rounded-xl font-bold text-base transition-all flex items-center group transform">
                        <span>Kirim Pengajuan</span>
                        <i class="fas fa-paper-plane ml-3 text-sm group-hover:translate-x-1 group-hover:-translate-y-0.5 transition-transform"></i>
                    </button>
                @else
                    {{-- JIKA TAHUN LALU: Tombol Kirim Hilang, Diganti Info Arsip --}}
                    <div class="bg-amber-50 border border-amber-200 text-amber-600 font-black py-3.5 px-6 rounded-xl flex items-center text-sm tracking-widest uppercase shadow-sm">
                        <i class="fas fa-lock mr-2 text-lg"></i> Mode Arsip {{ $tahunPilihan }} (Read-Only)
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
    .animate-bounce-short {
        animation: bounce-short 0.5s ease-out 1;
    }
    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    [x-cloak] { display: none !important; }
</style>
@endsection