@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">MONITORING KONFIRMASI SKPP</h1>
        <p class="text-gray-500 mt-2 text-lg italic">Gunakan filter di bawah, tekan Enter untuk memproses.</p>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
        <form action="{{ route('satker.monitoring.skpp') }}" method="GET" class="flex flex-wrap lg:flex-nowrap gap-4 items-end w-full">
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Bulan Periode</label>
                <input type="text" name="bulan" value="{{ request('bulan') }}" placeholder="JANUARI {{ $tahunAktif }}" 
                       class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all uppercase">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Jenis Pegawai</label>
                <select name="jenis_pegawai" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                    <option value="">-- Semua --</option>
                    <option value="PNS" {{ request('jenis_pegawai') == 'PNS' ? 'selected' : '' }}>PNS</option>
                    <option value="PPPK" {{ request('jenis_pegawai') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    <option value="TNI" {{ request('jenis_pegawai') == 'TNI' ? 'selected' : '' }}>TNI</option>
                    <option value="POLRI" {{ request('jenis_pegawai') == 'POLRI' ? 'selected' : '' }}>POLRI</option>
                </select>
            </div>

            <div class="flex-1 min-w-[250px]">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Jenis SKPP</label>
                <select name="jenis_skpp" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium text-xs">
                    <option value="">-- Semua Jenis SKPP --</option>
                    <option value="Pindah" {{ request('jenis_skpp') == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                    <option value="Pensiun" {{ request('jenis_skpp') == 'Pensiun' ? 'selected' : '' }}>Pensiun</option>
                    <option value="Berhenti Non Pensiun" {{ request('jenis_skpp') == 'Berhenti Non Pensiun' ? 'selected' : '' }}>Berhenti Non Pensiun</option>
                    <option value="Meninggal Berhak Pensiun" {{ request('jenis_skpp') == 'Meninggal Berhak Pensiun' ? 'selected' : '' }}>Meninggal Berhak Pensiun</option>
                    <option value="Meninggal Tidak Berhak Pensiun" {{ request('jenis_skpp') == 'Meninggal Tidak Berhak Pensiun' ? 'selected' : '' }}>Meninggal Tidak Berhak Pensiun</option>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">-- Semua --</option>
                    <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Diproses" {{ request('status') == 'Diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex flex-none gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md shadow-blue-500/30">
                    Filter
                </button>
                <a href="{{ route('satker.monitoring.skpp') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-[35px] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[500px] custom-scrollbar relative">
            <table class="w-full text-left border-collapse min-w-[2400px]">
                <thead class="sticky top-0 z-20 bg-white">
                    <tr class="bg-gray-50 border-b border-gray-100 uppercase text-[11px] font-black text-gray-500 tracking-wider">
                        <th class="px-6 py-5">Timestamp</th>
                        <th class="px-6 py-5">Nomor Tiket</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-6 py-5">Nama Operator</th>
                        <th class="px-6 py-5">No. WhatsApp</th>
                        <th class="px-6 py-5">Anak Satker</th>
                        <th class="px-6 py-5">Jenis Pegawai</th>
                        <th class="px-6 py-5 text-center">ID SKPP</th>
                        <th class="px-6 py-5">Jenis SKPP</th>
                        <th class="px-6 py-5">Nomor SKPP</th>
                        <th class="px-6 py-5">Nama Pegawai</th>
                        <th class="px-6 py-5 text-center">Jml Pegawai</th>
                        <th class="px-6 py-5 text-center">Bulan Periode</th>
                        <th class="px-6 py-5 text-center">Berkas SKPP</th>
                        <th class="px-6 py-5">Catatan Satker</th>
                        <th class="px-6 py-5">Petugas KPPN</th>
                        <th class="px-6 py-5">Catatan Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($data as $d)
                    <tr class="transition-all {{ $d->status == 'Ditolak' ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-blue-50/30' }}">
                        <td class="px-6 py-5 text-xs font-medium text-gray-400 whitespace-nowrap">
                            {{ $d->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-5 text-xs font-black text-gray-700 whitespace-nowrap uppercase">
                            #{{ $d->nomor_tiket }}
                        </td>
                        <td class="px-6 py-5 text-center whitespace-nowrap">
                            @php
                                // --- LOGIKA ILUSI VISUAL UNTUK SATKER ---
                                $statusTampilan = ($d->status === 'Menunggu Approval') ? 'Diproses' : $d->status;

                                // Pewarnaan Badge Dinamis (Sama persis dengan Gaji Web)
                                $badge = [
                                    'Menunggu' => 'bg-amber-100 text-amber-700 border border-amber-200',
                                    'Diproses' => 'bg-blue-100 text-blue-700 border border-blue-200',
                                    'Selesai'  => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    'Ditolak'  => 'bg-red-600 text-white shadow-md shadow-red-500/30'
                                ][$statusTampilan] ?? 'bg-gray-100 text-gray-600';
                            @endphp

                            <span class="{{ $badge }} px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap inline-block text-center min-w-[100px]">
                                {{ $statusTampilan }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-gray-600 whitespace-nowrap uppercase">
                            {{ $d->nama_operator }}
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-gray-600 whitespace-nowrap uppercase">
                            {{ $d->no_whatsapp }}
                        </td>
                        
                        <td class="px-6 py-5 text-xs font-bold text-[#1e3a8a] whitespace-nowrap uppercase">
                            {{ $d->detailSkpp->anak_satker ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-gray-500 whitespace-nowrap uppercase">
                            {{ $d->detailSkpp->jenis_pegawai ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-center text-xs font-black text-gray-800 whitespace-nowrap tracking-widest">
                            {{ $d->detailSkpp->id_skpp ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-xs font-black text-blue-600 whitespace-nowrap uppercase">
                            {{ $d->detailSkpp->jenis_skpp ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-gray-600 whitespace-nowrap uppercase tracking-widest">
                            {{ $d->detailSkpp->nomor_skpp ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-[#1e3a8a] whitespace-nowrap uppercase">
                            {{ $d->detailSkpp->nama_pegawai ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-center text-xs font-bold text-gray-600 whitespace-nowrap">
                            {{ $d->detailSkpp->jumlah_pegawai ?? '-' }} Orang
                        </td>
                        <td class="px-6 py-5 text-center whitespace-nowrap">
                            <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-lg text-[10px] font-black uppercase">
                                {{ $d->detailSkpp->bulan_periode ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center whitespace-nowrap">
                            @php
                                $pathFile = $d->detailSkpp->file_kelengkapan ?? null;
                            @endphp

                            @if($d->detailSkpp && !empty($pathFile))
                                <a href="{{ route('dokumen.view', ['kategori' => 'skpp', 'id' => $d->id, 'filename' => 'Dokumen_SKPP_' . $d->id . '.pdf']) }}" target="_blank" 
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded-xl text-[10px] font-bold hover:bg-red-50 transition-all shadow-sm">
                                    <i class="fas fa-file-pdf mr-2 text-sm"></i> BUKA FILE
                                </a>
                            @else
                                <span class="text-gray-300 text-[10px] font-bold italic uppercase tracking-tighter">No File</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-xs text-gray-500 italic min-w-[200px]">
                            {{ $d->catatan ?? '-' }}
                        </td>
                        <td class="px-6 py-5 text-xs font-bold text-gray-700 whitespace-nowrap uppercase">
                            {{ $d->admin->nama_lengkap ?? 'Menunggu PIC' }}
                        </td>
                        <td class="px-6 py-5 text-xs min-w-[250px]">
                            @if($d->status == 'Ditolak')
                                <span class="text-red-700 font-bold leading-snug uppercase">{{ $d->catatan_admin ?? '-' }}</span>
                            @else
                                <span class="text-gray-500 leading-snug uppercase">{{ $d->catatan_admin ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="px-6 py-32 text-center text-gray-300 font-bold italic">Data Konfirmasi SKPP tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-6">
        {{ $data->appends(request()->query())->links() }}
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 12px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fbff; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; border: 3px solid #f8fbff; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection