@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-4xl font-black text-[#1e3a8a] tracking-tight">Form Rekon Gaji Web</h2>
            <p class="text-gray-500 mt-2 text-lg italic">Lengkapi formulir di bawah ini sesuai dengan data ADK Anda.</p>
        </div>
        <a href="{{ route('dashboard.satker') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="mb-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl shadow-sm">
        <h3 class="text-blue-900 font-black text-lg mb-1">KPPN Kendari</h3>
        <ul class="text-[13px] text-blue-800 list-none space-y-1 font-medium italic leading-relaxed">
            <li>- Konfirmasi hanya dilakukan satu kali.</li>
            <li>- Konfirmasi ganda akan menyebabkan ADK tidak dapat diproses.</li>
            <li class="pt-2 font-bold italic">Hubungi CSO atau sampaikan keluhan pada grup whatsapp satker apabila mengalami kendala</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="mb-8 p-5 bg-green-50 border-l-4 border-green-500 rounded-r-2xl shadow-sm flex items-start animate-bounce-short text-green-800 font-bold">
            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-4 text-2xl"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="bg-white p-10 rounded-[35px] shadow-sm border border-gray-100" 
        x-data="{ 
            jenisProses: '', 
            kategoriAdk: '',
            fileName: '',
            fileError: false,
            noWhatsapp: '',
            isWaError: false,
            isIdError: false, 
            opsiRekon: [
                { val: 'ADK Gaji (.gpp)', label: 'ADK Gaji (.gpp)' },
                { val: 'ADK Penyamaan Data (.pgw / .kgw)', label: 'ADK Penyamaan Data (.pgw / .kgw)' },
                { val: 'ADK Pegawai Baru (.krm / .bru / .kkk)', label: 'ADK Pegawai Baru (.krm / .bru / .kkk)' },
                { val: 'ADK Kelengkapan SK (.sk)', label: 'ADK Kelengkapan SK (.sk)' },
                { val: 'ADK Perbaikan NIP (.kor)', label: 'ADK Perbaikan NIP (.kor)' }
            ],
            opsiBatal: [
                { val: 'ADK Gaji (.gpp)', label: 'ADK Gaji (.gpp)' },
                { val: 'ADK Pegawai Baru (.krm / .bru / .kkk) yang masih gantung / belum disetujui', label: 'ADK Pegawai Baru (.krm / .bru / .kkk) yang masih gantung / belum disetujui' },
                { val: 'ADK Kelengkapan SK (.sk) yang masih gantung / belum disetujui', label: 'ADK Kelengkapan SK (.sk) yang masih gantung / belum disetujui' },
                { val: 'ADK Perbaikan NIP (.kor) yang masih gantung / belum disetujui', label: 'ADK Perbaikan NIP (.kor) yang masih gantung / belum disetujui' }
            ]
        }">
        
        <form action="{{ route('satker.gajiweb.store') }}" method="POST" enctype="multipart/form-data">
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

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">2. Rincian Rekonsiliasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Kategori Pegawai <span class="text-red-500">*</span></label>
                    <select name="jenis_pegawai" required class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                        <option value="" disabled selected>-- Pilih Jenis Pegawai --</option>
                        <option value="PNS">PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="POLRI">POLRI</option>
                        <option value="TNI">TNI</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Jenis Proses <span class="text-red-500">*</span></label>
                    <select name="jenis_proses" x-model="jenisProses" @change="kategoriAdk = ''" required 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-sm">
                        <option value="" disabled selected>-- Pilih Jenis Proses --</option>
                        <option value="Rekon ADK">Rekon ADK</option>
                        <option value="Penghapusan/Pembatalan ADK">Penghapusan / Pembatalan ADK</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Kategori ADK <span class="text-red-500">*</span></label>
                    <div x-show="jenisProses === 'Rekon ADK'" x-transition class="bg-amber-50 border-l-4 border-amber-400 p-2.5 mb-3 rounded-r-lg">
                        <p class="text-[11px] text-amber-800 font-medium leading-tight italic">
                            <i class="fas fa-info-circle mr-1"></i> Khusus pilihan <b>ADK Penyamaan Data (.pgw)</b>, hanya dikirimkan apabila terdapat Kode Satker baru.
                        </p>
                    </div>

                    <select name="kategori_adk" x-model="kategoriAdk" required :disabled="!jenisProses"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-[13px] disabled:opacity-50">
                        <option value="" disabled selected>-- Pilih Kategori ADK --</option>
                        <template x-if="jenisProses === 'Rekon ADK'">
                            <template x-for="item in opsiRekon" :key="item.val">
                                <option :value="item.val" x-text="item.label"></option>
                            </template>
                        </template>
                        <template x-if="jenisProses === 'Penghapusan/Pembatalan ADK'">
                            <template x-for="item in opsiBatal" :key="item.val">
                                <option :value="item.val" x-text="item.label"></option>
                            </template>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-1">Bulan Periode <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-gray-400 mb-2 italic leading-tight">Format: NAMA BULAN + TAHUN (Wajib KAPITAL)<br>Contoh: JANUARI {{ $tahunAktif }}</p>
                    <input type="text" name="bulan_periode" placeholder="Contoh: JANUARI {{ $tahunAktif }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold uppercase">
                </div>
            </div>

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">3. Dokumen & Catatan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-4">Kelengkapan Dokumen Pendukung <span class="text-red-500">*</span></label>
                    
                    <div class="mb-6 text-[13px] text-gray-800">
                        <p class="font-bold mb-2">A. REKON ADK</p>
                        <ul class="list-disc ml-6 mb-5 space-y-1">
                            <li>Rekon Gaji (.gpp): unggah <b>DPP</b></li>
                            <li>Rekon Pegawai Baru (.krm/.bru/.kkk): unggah <b>SKPP</b></li>
                            <li>Rekon SK (.sk): unggah <b>SK</b></li>
                        </ul>

                        <p class="font-bold mb-2">B. PENGHAPUSAN GAJI</p>
                        <ul class="list-disc ml-6 mb-5 space-y-1">
                            <li>Unggah PDF berisi <b>DPP</b> dan <b>Alasan Penghapusan + Nomor Gaji</b></li>
                        </ul>

                        <p class="font-bold mb-2">C. PEMBATALAN ADK GANTUNG</p>
                        <ul class="list-disc ml-6 mb-5 space-y-1">
                            <li>Unggah PDF berisi <b>Alasan Penolakan</b></li>
                        </ul>
                    </div>

                    <p class="text-[12px] text-gray-500 mb-3">Upload 1 file yang didukung: PDF. Maks 5 MB.</p>

                    <div class="border-2 border-dashed rounded-3xl p-8 text-center transition-all group relative"
                         :class="fileName !== '' ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-500 hover:bg-blue-50'">
                        <input type="file" name="file_kelengkapan" accept=".pdf" required 
                            @change="
                                if ($event.target.files.length > 0) {
                                    let file = $event.target.files[0];
                                    if (file.size > 5242880) { // 5MB = 5 * 1024 * 1024 bytes
                                        fileError = true;
                                        fileName = '';
                                        $event.target.value = ''; // Reset file input
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
                            <p class="text-sm font-bold text-gray-600 tracking-tight">Klik untuk Tambahkan Berkas PDF</p>
                        </div>
                        <div x-show="fileName !== '' && !fileError" x-cloak>
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                            <p class="text-[13px] font-bold text-[#1e3a8a] truncate px-4" x-text="fileName"></p>
                        </div>
                        <div x-show="fileError" x-cloak class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-3 animate-pulse"></i>
                            <p class="text-sm font-bold tracking-tight">File kebesaran! Maksimal 5 MB.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-3">Catatan Satker</label>
                    <textarea name="catatan_satker" rows="10" placeholder="Tambahkan keterangan tambahan jika diperlukan..." 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-4 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium"></textarea>
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
    .animate-bounce-short { animation: bounce-short 0.5s ease-out 1; }
    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    [x-cloak] { display: none !important; }
</style>
@endsection