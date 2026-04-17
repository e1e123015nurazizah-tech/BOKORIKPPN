@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20" x-data="{ 
    modalProses: false, 
    dataDetail: {}, 
    actionUrl: '',
    mode: 'view',
    getKategoriLabel() {
        return 'Pengajuan Gaji Web';
    }
}">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">Monitoring Gaji Web</h1>
            <p class="text-gray-500 mt-1 text-sm italic">
                Layanan: <span class="text-blue-600 font-bold uppercase">Rekon Gaji Web</span>
            </p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-2xl mb-6">
            <ul class="list-disc pl-5 font-medium">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- MEJA KERJA ANDA --}}
    @if(isset($tugasAktif) && $tugasAktif->count() > 0)
    <div class="mb-10 bg-blue-50/40 p-6 md:p-8 rounded-[35px] border-2 border-blue-200 shadow-md relative overflow-hidden">
        <div class="absolute top-0 left-0 w-3 h-full bg-blue-500"></div>
        <div class="flex items-center justify-between mb-6 pl-2">
            <h2 class="text-base font-black text-[#1e3a8a] uppercase tracking-widest flex items-center">
                <i class="fas fa-briefcase mr-3 text-blue-600 text-2xl animate-pulse"></i> Meja Kerja Anda
            </h2>
            <span class="bg-blue-600 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-widest">
                {{ $tugasAktif->count() }} Sedang Diproses
            </span>
        </div>
        <div class="overflow-x-auto rounded-2xl bg-white shadow-sm border border-blue-100 max-h-[400px] overflow-y-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-separate border-spacing-0">
                <thead class="bg-blue-100/50 text-[#1e3a8a] uppercase text-[10px] tracking-widest border-b border-blue-100 sticky top-0 z-10 backdrop-blur-md">
                    <tr>
                        <th class="py-4 px-5 font-black bg-blue-50/90">Info Tiket</th>
                        <th class="py-4 px-5 font-black bg-blue-50/90">Satuan Kerja</th>
                        <th class="py-4 px-5 text-center font-black bg-blue-50/90">Aksi Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($tugasAktif as $tugas)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="py-4 px-5">
                            <div class="font-black text-blue-700 text-base tracking-tighter">{{ $tugas->nomor_tiket }}</div>
                        </td>
                        <td class="py-4 px-5">
                            <div class="font-bold text-gray-700 uppercase">{{ $tugas->satker->nama_satker }}</div>
                            {{-- Kode Satker di Meja Kerja --}}
                            <div class="text-[11px] font-mono font-bold text-emerald-600 mt-0.5 mb-1">
                                Kode: {{ $tugas->satker->kode_satker }}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1 italic">
                                <i class="far fa-clock mr-1"></i> Diambil {{ \Carbon\Carbon::parse($tugas->waktu_diambil)->diffForHumans() }}
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex justify-center gap-2">
                                <button @click="
                                    modalProses = true; 
                                    mode = 'proses';
                                    dataDetail = {{ json_encode($tugas) }}; 
                                    actionUrl = '{{ route('admin.pengajuan.proses', $tugas->id) }}';
                                " class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black py-2.5 px-5 rounded-xl transition-all shadow-md active:scale-95 uppercase tracking-widest">
                                    <i class="fas fa-hammer mr-1"></i> Kerjakan
                                </button>
                                
                                <form action="{{ route('admin.pengajuan.lepas', $tugas->id) }}" method="POST" onsubmit="return confirm('Yakin ingin melepas tiket ini kembali ke antrean?');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300 text-[10px] font-black py-2.5 px-4 rounded-xl transition-all shadow-sm active:scale-95 uppercase tracking-widest">
                                        Lepas
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TABEL DATA GAJI WEB (DENGAN SCROLL & HAPUS) --}}
    <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100">
        
        {{-- FORM FILTER PENCARIAN & STATUS (AUTO-SUBMIT) --}}
        <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between border-b border-gray-100 pb-6">
            <form id="formFilterPengajuan" action="{{ url()->current() }}" method="GET" class="w-full flex flex-col md:flex-row gap-3 relative">
                
                {{-- Bawa parameter kategori agar tidak salah kamar --}}
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif

                {{-- Input Pencarian (Otomatis cari setelah berhenti mengetik 0.8 detik) --}}
                <div class="relative w-full md:w-1/2">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" 
                           @input.debounce.800ms="$event.target.form.submit()"
                           placeholder="Cari Kode Satker, Nama, atau No Tiket..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all shadow-sm">
                </div>

                {{-- Dropdown Status (Otomatis filter saat opsi dipilih) --}}
                <div class="w-full md:w-1/4">
                    <select name="status" onchange="this.form.submit()" 
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all text-gray-600 cursor-pointer shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu (Baru)</option>
                        <option value="Diproses" {{ request('status') == 'Diproses' ? 'selected' : '' }}>Diproses (Sedang Dikerjakan)</option>
                        <option value="Menunggu Approval" {{ request('status') == 'Menunggu Approval' ? 'selected' : '' }}>Menunggu Approval</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                {{-- Tombol Reset (Muncul jika ada pencarian/filter aktif) --}}
                @if(request('search') || request('status'))
                    <a href="{{ url()->current() }}?kategori={{ request('kategori') }}" class="text-sm text-red-500 hover:text-red-700 font-bold bg-red-50 py-3 px-4 rounded-xl transition-colors whitespace-nowrap flex items-center shadow-sm">
                        <i class="fas fa-times mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>
        {{-- Script agar Kursor tetap berada di kotak pencarian setelah auto-reload --}}
        @if(request('search'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let input = document.getElementById('searchInput');
                if(input) {
                    input.focus();
                    let val = input.value;
                    input.value = '';
                    input.value = val;
                }
            });
        </script>
        @endif

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative pr-2">
            <table class="w-full text-left text-sm border-separate border-spacing-0">
                <thead class="text-gray-400 uppercase text-[10px] tracking-widest sticky top-0 z-10 bg-white shadow-sm">
                    <tr>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Info Tiket</th>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Satuan Kerja</th>
                        <th class="py-5 px-4 font-black text-center bg-white border-b border-gray-100">Status</th>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Petugas (Admin)</th>
                        <th class="py-5 px-4 text-center font-black bg-white border-b border-gray-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengajuans as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="py-5 px-4">
                            <div class="font-black text-[#1e3a8a] text-base tracking-tighter">{{ $item->nomor_tiket }}</div>
                        </td>
                        <td class="py-5 px-4">
                            <div class="font-bold text-gray-700 uppercase">{{ $item->satker->nama_satker }}</div>
                            
                            {{-- KODE SATKER DITAMBAHKAN DI SINI --}}
                            <div class="text-[11px] font-mono font-bold text-emerald-600 mt-0.5 mb-1">
                                Kode: {{ $item->satker->kode_satker }}
                            </div>
                            
                            <div class="text-[10px] text-gray-400 mt-1 italic">
                                <i class="far fa-clock mr-1"></i> Diajukan {{ $item->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="py-5 px-4 text-center">
                            @php
                                $badge = [
                                    'Menunggu' => 'bg-amber-100 text-amber-600 border-amber-200',
                                    'Diproses' => 'bg-blue-100 text-blue-600 border-blue-200',
                                    'Menunggu Approval' => 'bg-purple-100 text-purple-600 border-purple-200',
                                    'Selesai'  => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                    'Ditolak'  => 'bg-red-100 text-red-600 border-red-200'
                                ][$item->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="{{ $badge }} border px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="py-5 px-4">
                            @if($item->admin)
                                <span class="text-xs font-bold text-gray-600 uppercase">{{ $item->admin->nama_lengkap }}</span>
                            @else
                                <span class="text-xs text-gray-300 italic">Belum ada petugas</span>
                            @endif
                        </td>
                        <td class="py-5 px-4 text-center">
                            <div class="flex justify-center items-center gap-2">
                                @php
                                    $userLogin = Auth::guard('admin')->user();
                                    $isOwner = $item->admin_id === $userLogin->id;
                                    $isSuperAdmin = $userLogin->role === 'superadmin';
                                @endphp

                                @if($item->status === 'Menunggu')
                                    @if($userLogin->role !== 'approver')
                                        <form action="{{ route('admin.pengajuan.ambil', $item->id) }}" method="POST" class="m-0">
                                            @csrf @method('PUT')
                                            <button type="submit" class="bg-[#1e3a8a] hover:bg-blue-800 text-white text-[10px] font-black py-2.5 px-4 rounded-lg transition-all shadow-sm active:scale-95 uppercase tracking-wide whitespace-nowrap flex items-center justify-center">
                                                <i class="fas fa-hand-holding-medical mr-1.5"></i> Ambil Pengajuan
                                            </button>
                                        </form>
                                    @endif
                                @elseif($item->status === 'Diproses')
                                    @if($isOwner || $isSuperAdmin)
                                        <button @click="
                                            modalProses = true; 
                                            mode = 'proses';
                                            dataDetail = {{ json_encode($item) }}; 
                                            actionUrl = '{{ route('admin.pengajuan.proses', $item->id) }}';
                                        " class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-black py-2.5 px-4 rounded-xl transition-all shadow-md active:scale-95 uppercase tracking-wider">
                                            Kerjakan
                                        </button>
                                        <form action="{{ route('admin.pengajuan.lepas', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin melepas tiket ini?');">
                                            @csrf @method('PUT')
                                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black py-2.5 px-4 rounded-xl transition-all shadow-md active:scale-95 uppercase tracking-wider">
                                                Lepas
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[9px] font-bold text-gray-400 uppercase italic">Sedang Diproses</span>
                                    @endif
                                @else
                                    <button @click="
                                        modalProses = true; mode = 'view'; dataDetail = {{ json_encode($item) }}; actionUrl = '';
                                    " class="text-gray-400 hover:text-blue-600 transition-colors p-2" title="Lihat Detail">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>

                                    @if($isOwner || $isSuperAdmin || $userLogin->role === 'approver')
                                        <button @click="
                                            modalProses = true; mode = 'edit'; dataDetail = {{ json_encode($item) }}; 
                                            actionUrl = '{{ route('admin.pengajuan.proses', $item->id) }}';
                                        " class="text-gray-400 hover:text-amber-500 transition-colors p-2" title="Ubah Keputusan">
                                            <i class="fas fa-edit text-lg"></i>
                                        </button>
                                    @endif
                                @endif

                                @if($isSuperAdmin || $item->status === 'Menunggu')
                                    <form action="{{ route('admin.pengajuan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('PERINGATAN! Yakin ingin menghapus tiket ini secara permanen? Data akan hilang dari KPPN dan Satker.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors p-2" title="Hapus Permanen">
                                            <i class="fas fa-trash-alt text-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-20 text-gray-300 italic font-medium">
                            <i class="fas fa-inbox text-4xl mb-3 block opacity-20"></i>
                            @if(request('search') || request('status'))
                                Tidak ada data yang sesuai dengan filter pencarian Anda.
                            @else
                                Belum ada data Gaji Web.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-8">
            {{ $pengajuans->appends(['kategori' => request('kategori')])->links() }}
        </div>
    </div>

    {{-- MODAL PROSES KHUSUS GAJI WEB --}}
    <div x-show="modalProses" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalProses = false" class="bg-white rounded-[35px] w-full max-w-4xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl transform transition-all custom-scrollbar" x-transition.scale.origin.center>
            
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight" x-text="(mode === 'edit' ? 'Ubah Keputusan ' : (mode === 'view' ? 'Detail ' : 'Verifikasi ')) + getKategoriLabel()"></h3>
                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest" x-text="'ID TIKET: ' + dataDetail.nomor_tiket"></p>
                </div>
                <button type="button" @click="modalProses = false" class="text-gray-300 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form :action="actionUrl" method="POST">
                @csrf @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 shadow-inner">
                            <h4 class="text-[10px] font-black text-blue-600 uppercase mb-5 tracking-widest flex items-center">
                                <i class="fas fa-info-circle mr-2"></i> Rincian Pengajuan Satker
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-5 text-xs">
                                <div class="col-span-1">
                                    <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Operator PIC</p>
                                    <p class="font-bold text-gray-700 uppercase" x-text="dataDetail.nama_operator"></p>
                                </div>
                                <div class="col-span-1 text-right">
                                    <p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">No. WhatsApp</p>
                                    <p class="font-bold text-gray-700" x-text="dataDetail.no_whatsapp"></p>
                                </div>
                            </div>
                                
                            {{-- HANYA INCLUDE GAJI WEB --}}
                            @include('admin.pengajuan.partials._detail_gajiweb')

                            <div class="mt-8">
                                <a :href="'/storage/' + (dataDetail.detail_gaji?.file_kelengkapan || '')" 
                                target="_blank" 
                                class="flex items-center justify-center gap-3 bg-red-50 text-red-600 font-black py-4 rounded-2xl border border-red-100 hover:bg-red-100 transition-all text-xs uppercase tracking-widest shadow-sm">
                                    <i class="fas fa-file-pdf text-lg"></i> Buka Berkas Lampiran PDF
                                </a>
                            </div>
                        </div>

                        <div class="bg-amber-50/50 p-5 rounded-2xl border border-amber-100/50 mt-6">
                            <p class="text-[9px] font-black text-amber-600 uppercase mb-2 tracking-widest">Pesan/Catatan Satker:</p>
                            <p class="text-xs text-amber-900 leading-relaxed italic" x-text="dataDetail.detail_gaji?.catatan_satker || 'Tidak ada catatan tambahan.'"></p>
                        </div>
                    </div>

                    <div class="flex flex-col h-full">
                        <div class="bg-blue-50/30 p-6 rounded-[2rem] border border-blue-100 flex-1">
                            <h4 class="text-[10px] font-black text-[#1e3a8a] uppercase mb-6 tracking-widest flex items-center">
                                <i class="fas fa-user-check mr-2"></i> Panel Keputusan Admin
                            </h4>
                            
                            <template x-if="mode === 'proses' || mode === 'edit'">
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-wider">Tentukan Status Akhir</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative">
                                                <input type="radio" name="status" value="Selesai" class="peer hidden" :checked="dataDetail.status === 'Selesai'" required>
                                                <div class="text-center py-3 rounded-xl border-2 border-gray-100 text-gray-400 font-bold text-xs cursor-pointer peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-600 transition-all uppercase">Setujui</div>
                                            </label>
                                            <label class="relative">
                                                <input type="radio" name="status" value="Ditolak" class="peer hidden" :checked="dataDetail.status === 'Ditolak'">
                                                <div class="text-center py-3 rounded-xl border-2 border-gray-100 text-gray-400 font-bold text-xs cursor-pointer peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-600 transition-all uppercase">Tolak</div>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-wider">Catatan Balasan (Feedback)</label>
                                        <textarea name="catatan" rows="6" :value="dataDetail.catatan"
                                                  class="w-full bg-white border border-gray-200 py-4 px-5 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-gray-300"
                                                  placeholder="Wajib diisi jika ditolak..."></textarea>
                                    </div>
                                </div>
                            </template>

                            <template x-if="mode === 'view'">
                                <div class="text-center py-10">
                                    <div class="inline-block p-4 rounded-full mb-4" :class="dataDetail.status === 'Selesai' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                                        <i class="fas fa-3xl" :class="dataDetail.status === 'Selesai' ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    </div>
                                    <p class="font-black uppercase tracking-widest text-[#1e3a8a]" x-text="'Pengajuan ' + dataDetail.status"></p>
                                    <div class="mt-4 p-4 bg-white rounded-2xl border border-gray-100 text-left">
                                        <p class="text-[9px] font-bold text-gray-400 uppercase mb-1">Catatan Admin:</p>
                                        <p class="text-xs text-gray-600 italic" x-text="dataDetail.catatan || 'Tidak ada catatan.'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="mode === 'proses' || mode === 'edit'">
                            <div class="flex gap-4 mt-8">
                                <button type="button" @click="modalProses = false" class="flex-1 bg-gray-100 text-gray-600 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all uppercase text-[10px] tracking-widest">Batal</button>
                                <button type="submit" class="flex-[2] bg-blue-600 text-white font-black py-4 rounded-2xl hover:bg-blue-700 shadow-xl shadow-blue-600/20 transition-all uppercase text-[10px] tracking-[0.2em] active:scale-95">Simpan Keputusan</button>
                            </div>
                        </template>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 12px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; border: 2px solid transparent; background-clip: padding-box; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection