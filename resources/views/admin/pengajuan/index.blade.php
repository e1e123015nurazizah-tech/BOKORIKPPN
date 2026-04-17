@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20" x-data="{ 
    modalProses: false, 
    dataDetail: {}, 
    actionUrl: '', 
    mode: 'view',
    getKategoriLabel() {
        return this.dataDetail.kategori_layanan || 'Pengajuan';
    }
}">
    @php $roleAdmin = Auth::guard('admin')->user()->role; @endphp

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">Monitoring Semua Layanan</h1>
            <p class="text-gray-500 mt-1 text-sm italic">
                Menampilkan seluruh data pengajuan <span class="text-blue-600 font-bold uppercase">Gaji Web, PPNPN, & SKPP</span>
            </p>
        </div>

        {{-- TOMBOL DOWNLOAD DINAMIS BERDASARKAN ROLE --}}
        @if($roleAdmin === 'approver')
            <a href="{{ route('admin.pengajuan.export_approver') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-purple-500/30 flex items-center active:scale-95 text-xs uppercase tracking-widest">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Download SKPP {{ session('tahun_aktif', date('Y')) }}
            </a>
        @else
            <a href="{{ route('admin.pengajuan.export_semua') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-500/30 flex items-center active:scale-95 text-xs uppercase tracking-widest">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Download Excel {{ session('tahun_aktif', date('Y')) }}
            </a>
        @endif
    </div>

    {{-- STATISTIK OVERVIEW --}}
    <div class="grid grid-cols-2 {{ $roleAdmin === 'approver' ? 'md:grid-cols-3' : 'md:grid-cols-5' }} gap-4 mb-8">
        @if($roleAdmin === 'approver')
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-purple-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Butuh Otorisasi</p>
                    <h3 class="text-2xl font-black text-purple-500">{{ $stats['menunggu_approval'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-500"><i class="fas fa-check-double text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-emerald-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Selesai Otorisasi</p>
                    <h3 class="text-2xl font-black text-emerald-500">{{ $stats['selesai'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500"><i class="fas fa-check-circle text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-red-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">SKPP Ditolak</p>
                    <h3 class="text-2xl font-black text-red-500">{{ $stats['ditolak'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-500"><i class="fas fa-times-circle text-xl"></i></div>
            </div>
        @else
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-amber-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Antrean Baru</p>
                    <h3 class="text-2xl font-black text-amber-500">{{ $stats['menunggu'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500"><i class="fas fa-inbox text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-blue-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Tugas Saya</p>
                    <h3 class="text-2xl font-black text-blue-600">{{ $stats['tugas_saya'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600"><i class="fas fa-tasks text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-purple-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Butuh Approval</p>
                    <h3 class="text-2xl font-black text-purple-500">{{ $stats['menunggu_approval'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-500"><i class="fas fa-check-double text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-emerald-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Disetujui</p>
                    <h3 class="text-2xl font-black text-emerald-500">{{ $stats['selesai'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500"><i class="fas fa-check-circle text-xl"></i></div>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between group hover:border-red-300 transition-colors">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Ditolak</p>
                    <h3 class="text-2xl font-black text-red-500">{{ $stats['ditolak'] ?? 0 }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-500"><i class="fas fa-times-circle text-xl"></i></div>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- TABEL SEMUA DATA (TANPA FILTER) --}}
    <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100">
        <div class="overflow-x-auto overflow-y-auto max-h-[600px] custom-scrollbar relative pr-2">
            <table class="w-full text-left text-sm border-separate border-spacing-0">
                <thead class="text-gray-400 uppercase text-[10px] tracking-widest sticky top-0 z-10 bg-white shadow-sm">
                    <tr>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Info Tiket</th>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Satuan Kerja</th>
                        <th class="py-5 px-4 font-black text-center bg-white border-b border-gray-100">Status</th>
                        <th class="py-5 px-4 font-black bg-white border-b border-gray-100">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengajuans as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="py-5 px-4">
                            <div class="font-black text-[#1e3a8a] text-base tracking-tighter">{{ $item->nomor_tiket }}</div>
                            <span class="text-[9px] font-black text-blue-500 uppercase">{{ $item->kategori_layanan }}</span>
                        </td>
                        <td class="py-5 px-4">
                            <div class="font-bold text-gray-700 uppercase">{{ $item->satker->nama_satker }}</div>
                            <div class="text-[11px] font-mono font-bold text-emerald-600 mt-0.5 mb-1">
                                Kode: {{ $item->satker->kode_satker }}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1 italic">Diajukan {{ $item->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="py-5 px-4 text-center">
                            @php
                                $badge = [
                                    'Menunggu' => 'bg-amber-100 text-amber-600',
                                    'Diproses' => 'bg-blue-100 text-blue-600',
                                    'Menunggu Approval' => 'bg-purple-100 text-purple-600',
                                    'Selesai'  => 'bg-emerald-100 text-emerald-600',
                                    'Ditolak'  => 'bg-red-100 text-red-600'
                                ][$item->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="{{ $badge }} px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap">{{ $item->status }}</span>
                        </td>
                        <td class="py-5 px-4">
                            @if($item->admin)
                                <span class="text-xs font-bold text-gray-600 uppercase">{{ $item->admin->nama_lengkap }}</span>
                            @else
                                <span class="text-xs text-gray-300 italic">Belum ada petugas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-20 text-gray-300 italic font-medium">
                            Belum ada data pengajuan di tahun {{ session('tahun_aktif', date('Y')) }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-8">{{ $pengajuans->links() }}</div>
    </div>

    {{-- MODAL PROSES DINAMIS --}}
    <div x-show="modalProses" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalProses = false" class="bg-white rounded-[35px] w-full max-w-4xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl custom-scrollbar" x-transition.scale.origin.center>
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div>
                    <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight" x-text="(mode === 'edit' ? 'Ubah Keputusan ' : 'Verifikasi ') + getKategoriLabel()"></h3>
                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase" x-text="'ID TIKET: ' + dataDetail.nomor_tiket"></p>
                </div>
                <button type="button" @click="modalProses = false" class="text-gray-300 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form :action="actionUrl" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                            <h4 class="text-[10px] font-black text-blue-600 uppercase mb-5 tracking-widest"><i class="fas fa-info-circle mr-2"></i> Rincian Pengajuan</h4>
                            <div class="grid grid-cols-2 gap-5 text-xs mb-5">
                                <div><p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">Operator</p><p class="font-bold text-gray-700 uppercase" x-text="dataDetail.nama_operator"></p></div>
                                <div class="text-right"><p class="text-gray-400 mb-1 font-bold uppercase text-[9px]">WhatsApp</p><p class="font-bold text-gray-700" x-text="dataDetail.no_whatsapp"></p></div>
                            </div>
                            @include('admin.pengajuan.partials._detail_gajiweb')
                            @include('admin.pengajuan.partials._detail_ppnpn')
                            @include('admin.pengajuan.partials._detail_skpp')
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="bg-blue-50/30 p-6 rounded-[2rem] border border-blue-100 flex-1">
                            <h4 class="text-[10px] font-black text-[#1e3a8a] uppercase mb-6 tracking-widest"><i class="fas fa-user-check mr-2"></i> Panel Keputusan</h4>
                            <template x-if="mode === 'proses' || mode === 'edit'">
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase">Pilih Status</label>
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
                                    <textarea name="catatan" rows="6" :value="dataDetail.catatan" class="w-full bg-white border border-gray-200 p-4 rounded-2xl text-sm outline-none transition-all" placeholder="Berikan catatan jika ada..."></textarea>
                                </div>
                            </template>
                            <template x-if="mode === 'view'">
                                <div class="text-center py-10">
                                    <p class="font-black uppercase tracking-widest text-[#1e3a8a]" x-text="'Pengajuan ' + dataDetail.status"></p>
                                    <p class="text-xs text-gray-600 mt-4 italic" x-text="dataDetail.catatan || 'Tidak ada catatan.'"></p>
                                </div>
                            </template>
                        </div>
                        <template x-if="mode === 'proses' || mode === 'edit'">
                            <div class="flex gap-4 mt-8">
                                <button type="button" @click="modalProses = false" class="flex-1 bg-gray-100 text-gray-600 font-bold py-4 rounded-2xl uppercase text-[10px]">Batal</button>
                                <button type="submit" class="flex-[2] bg-blue-600 text-white font-black py-4 rounded-2xl uppercase text-[10px]">Simpan</button>
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