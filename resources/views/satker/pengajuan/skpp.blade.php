@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-4xl font-black text-[#1e3a8a] tracking-tight">Form Rekon SKPP</h2>
            <p class="text-gray-500 mt-2 text-lg italic">Lengkapi formulir di bawah ini sesuai dengan data SKPP Anda.</p>
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

    <div class="mb-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl shadow-sm">
        <h4 class="text-sm font-bold text-[#1e3a8a] mb-2">KPPN Kendari</h4>
        <ul class="text-[12px] text-blue-900 list-none space-y-1 font-medium">
            <li>- Konfirmasi dilakukan untuk setiap SKPP yang diajukan jadi untuk dua dokumen skpp maka konfirmasi dua-duanya</li>
            <li>- Harap melakukan konfirmasi hanya satu kali</li>
            <li>- Konfirmasi ganda berakibat tidak dapat diproses</li>
            <li>- Konfirmasi hanya dilakukan ketika SKPP sudah berhasil kirim ke KPPN</li>
            <li class="pt-2 font-bold italic">Hubungi CSO atau sampaikan keluhan pada grup whatsapp satker apabila mengalami kendala</li>
        </ul>
    </div>

    <div class="bg-white p-10 rounded-[35px] shadow-sm border border-gray-100" 
         x-data="{ idSkpp: '', isIdError: false, fileName: '',fileError: false, noWhatsapp: '', isWaError: false }">
        
        <form action="{{ route('satker.skpp.store') }}" method="POST" enctype="multipart/form-data">
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

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">2. Rincian Data SKPP</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Anak Satker <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic">Contoh: 05-YONIF 725/WRG, 14-Fakultas Kehutanan</p>
                    <input type="text" name="anak_satker" placeholder="Masukkan Nama Anak Satker" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Jenis Pegawai <span class="text-red-500">*</span></label>
                    <select name="jenis_pegawai" required class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                        <option value="" disabled selected>-- Pilih Jenis Pegawai --</option>
                        <option value="PNS">PNS</option>
                        <option value="TNI">TNI</option>
                        <option value="POLRI">POLRI</option>
                        <option value="PPPK">PPPK</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">ID SKPP <span class="text-red-500">*</span></label>
                    <input type="text" name="id_skpp" placeholder="Hanya angka" required 
                           x-model="idSkpp" @input="isIdError = idSkpp !== '' && !/^\d+$/.test(idSkpp)"
                           :class="isIdError ? 'border-red-500 bg-red-50 text-red-600' : 'border-gray-200 bg-gray-50 text-gray-700'"
                           class="w-full border py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 transition-all font-medium">
                    <p x-show="isIdError" x-cloak class="text-[11px] text-red-600 font-bold mt-2 animate-pulse">
                        <i class="fas fa-times-circle mr-1"></i> Hanya boleh berisi angka!
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Jenis SKPP <span class="text-red-500">*</span></label>
                    <select name="jenis_skpp" required class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-sm">
                        <option value="" disabled selected>-- Pilih Jenis SKPP --</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Pensiun">Pensiun</option>
                        <option value="Berhenti Non Pensiun">Berhenti Non Pensiun</option>
                        <option value="Meninggal Berhak Pensiun">Meninggal Berhak Pensiun</option>
                        <option value="Meninggal Tidak Berhak Pensiun">Meninggal Tidak Berhak Pensiun</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Nomor SKPP <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic">Contoh: 363/skpp/2024</p>
                    <input type="text" name="nomor_skpp" placeholder="Contoh: 363/skpp/2024" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Nama Pegawai <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_pegawai" placeholder="Nama Pegawai yang bersangkutan" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Jml Pegawai <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_pegawai" value="1" min="1" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-center">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Bulan Periode <span class="text-red-500">*</span></label>
                        <p class="text-[10px] text-gray-400 mb-2 italic leading-tight">Format: NAMA BULAN + TAHUN (Wajib KAPITAL)<br>Contoh: JANUARI {{ $tahunAktif }}</p>
                        <input type="text" name="bulan_periode" placeholder="Contoh: JANUARI {{ $tahunAktif }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold uppercase placeholder:font-normal">
                    </div>
                </div>
            </div>

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">3. Unggah Berkas</h3>
            <div class="mb-10">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Dokumen Pendukung <span class="text-red-500">*</span></label>
                <p class="text-[11px] text-gray-500 mb-4 italic leading-tight">Dokumen yang diupload berupa SK Pindah/Pensiun dan Surat Permintaan Penonaktifan Supplier dalam satu PDF</p>
                
                <div class="border-2 border-dashed rounded-3xl p-8 text-center transition-all group relative"
                     :class="fileName !== '' && !fileError ? 'border-green-400 bg-green-50' : (fileError ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-blue-500 hover:bg-blue-50')">
                    <input type="file" name="file_kelengkapan" accept=".pdf" required 
                           @change="
                               if ($event.target.files.length > 0) {
                                   let file = $event.target.files[0];
                                   if (file.size > 5242880) { // 5 MB = 5 * 1024 * 1024 bytes
                                       fileError = true;
                                       fileName = '';
                                       $event.target.value = ''; 
                                       alert('Gagal: Ukuran file PDF terlalu besar! Maksimal 5 MB.');
                                   } else {
                                       fileError = false;
                                       fileName = file.name;
                                   }
                               } else {
                                   fileName = '';
                                   fileError = false;
                               }
                           "
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div x-show="fileName === '' && !fileError">
                        <i class="fas fa-file-pdf text-4xl text-gray-300 group-hover:text-red-500 mb-4 transition-colors"></i>
                        <p class="text-sm font-bold text-gray-600">Klik untuk Tambahkan Berkas PDF SKPP</p>
                        <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-widest font-bold">Maksimal 5 MB</p>
                    </div>
                    <div x-show="fileName !== '' && !fileError" x-cloak>
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                        <p class="text-sm font-bold text-[#1e3a8a]" x-text="fileName"></p>
                        <p class="text-[10px] text-green-600 mt-2 font-bold uppercase tracking-widest">Berkas Siap Dikirim</p>
                    </div>
                    <div x-show="fileError" x-cloak class="text-red-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-3 animate-pulse"></i>
                        <p class="text-sm font-bold tracking-tight">File kebesaran! Maksimal 5 MB.</p>
                    </div>
                </div>>
            </div>

            @php 
                $tahunAsli = date('Y');
                $tahunPilihan = session('tahun_aktif', $tahunAsli);
            @endphp

            <div class="flex items-center justify-end pt-6 border-t border-gray-100 mt-8">
                @if($tahunPilihan == $tahunAsli)
                    {{-- JIKA TAHUN SAMA: Munculkan Tombol Kirim Pengajuan Aslimu --}}
                    <button type="submit" 
                            :disabled="isIdError || isWaError || fileError"
                            :class="(isIdError || isWaError || fileError) 
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