@extends('layouts.dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="mt-4 pb-20" x-data="{ modalTambah: false, modalEdit: false, modalImport: false, editData: {}, actionUrlEdit: '' }">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">Kelola Satuan Kerja</h1>
            <p class="text-gray-500 mt-1 text-sm italic">Master data akun Satker pengelola layanan sistem BOKORI.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if(Auth::guard('admin')->user()->role === 'superadmin')
            
            {{-- Tombol Export Excel --}}
            <a href="{{ route('admin.satker.export') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Download Excel
            </a>

            {{-- Tombol Import Excel (Sudah diubah dari CSV) --}}
            <button @click="modalImport = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Import Excel
            </button>
            @endif

            <button @click="modalTambah = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-plus-circle mr-2 text-lg"></i> Tambah Satker Baru
            </button>
        </div>
    </div>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-6 flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-2xl mb-6 shadow-sm">
            <ul class="list-disc pl-5 font-medium">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100">
        
        {{-- FORM FILTER PENCARIAN SATKER (AUTO-SUBMIT) --}}
        <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between border-b border-gray-100 pb-6">
            <form action="{{ route('admin.satker.index') }}" method="GET" class="w-full md:w-1/2 relative flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" id="searchSatkerInput" value="{{ request('search') }}" 
                    @input.debounce.500ms="$event.target.form.submit()"
                    placeholder="Cari Kode Satker atau Nama..." 
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all shadow-sm">
            </form>
            
            @if(request('search'))
                <a href="{{ route('admin.satker.index') }}" class="text-sm text-red-500 hover:text-red-700 font-bold bg-red-50 py-2 px-4 rounded-lg transition-colors whitespace-nowrap">
                    <i class="fas fa-times mr-1"></i> Reset Pencarian
                </a>
            @endif
        </div>

        {{-- Script Fokus Kursor untuk Satker --}}
        @if(request('search'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let input = document.getElementById('searchSatkerInput');
                if(input) {
                    input.focus();
                    let val = input.value;
                    input.value = '';
                    input.value = val;
                }
            });
        </script>
        @endif

        <div class="overflow-x-auto overflow-y-auto max-h-[500px] pr-2">
            <table class="w-full text-left text-sm relative">
                <thead class="text-gray-400 uppercase text-[10px] tracking-widest border-b border-gray-100 sticky top-0 bg-white z-10">
                    <tr>
                        <th class="pb-5 px-4 font-black w-40">Kode Satker</th>
                        <th class="pb-5 px-4 font-black">Satuan Kerja & Petugas</th>
                        <th class="pb-5 px-4 text-center font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($data as $satker)
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        
                        <td class="py-5 px-4">
                            <span class="bg-slate-50 text-slate-600 font-mono text-xs font-bold px-3 py-1.5 rounded-lg tracking-widest border border-slate-200">
                                {{ $satker->kode_satker }}
                            </span>
                        </td>

                        <td class="py-5 px-4">
                            <div class="font-bold text-[#1e3a8a] uppercase text-base tracking-tight">{{ $satker->nama_satker }}</div>
                            <div class="text-[10px] mt-1 font-bold">
                                <i class="fas fa-user-shield text-gray-400 mr-1"></i> <span class="text-gray-400 uppercase tracking-widest">Petugas SKPP:</span> 
                                <span class="{{ $satker->petugasSkpp ? 'text-emerald-600' : 'text-amber-500 italic' }}">
                                    {{ $satker->petugasSkpp ? $satker->petugasSkpp->nama_lengkap : 'Belum Ditentukan' }}
                                </span>
                            </div>
                        </td>

                        <td class="py-5 px-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <button @click="
                                    modalEdit = true;
                                    editData = {{ json_encode($satker) }};
                                    actionUrlEdit = '{{ url('kelola-satker') }}/' + editData.id;
                                " class="text-blue-500 hover:text-blue-600 bg-blue-50 hover:bg-blue-100 p-2.5 rounded-xl transition-all shadow-sm">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('admin.satker.reset', $satker->id) }}" method="POST" onsubmit="return confirm('Reset password {{ $satker->nama_satker }} ke bawaan (satker123)?');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-amber-500 hover:text-amber-600 bg-amber-50 hover:bg-amber-100 p-2.5 rounded-xl transition-all shadow-sm">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.satker.destroy', $satker->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus Satker ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 p-2.5 rounded-xl transition-all shadow-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-20 text-gray-300 italic font-medium">
                            <i class="fas fa-folder-open text-4xl mb-3 block"></i>
                            @if(request('search'))
                                Tidak ada hasil untuk pencarian "{{ request('search') }}".
                            @else
                                Belum ada data Satuan Kerja terdaftar.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-8">
            {{ $data->links() }}
        </div>
    </div>

    @if(Auth::guard('admin')->user()->role === 'superadmin')
    <div x-show="modalImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalImport = false" class="bg-white rounded-[35px] w-full max-w-md p-8 shadow-2xl transform transition-all" x-transition.scale.origin.center>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-emerald-700 uppercase tracking-tight">Import Satker Massal (Excel)</h3>
                <button @click="modalImport = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <div class="mb-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                <p class="text-[11px] font-bold text-emerald-800 uppercase mb-2"><i class="fas fa-info-circle mr-1"></i> Format Kolom Excel (Baris 1):</p>
                <p class="text-[10px] text-emerald-600 leading-relaxed font-mono">
                    A: kode_satker | B: nama_satker | C: nama_petugas | D: password
                </p>
            </div>

            <form id="formImportSatker" action="{{ route('admin.satker.import') }}" method="POST" enctype="multipart/form-data" x-data="{ fileName: '' }">
                @csrf
                <div class="mb-8 text-center">
                    <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-3xl cursor-pointer transition-all group"
                        :class="fileName ? 'border-emerald-500 bg-emerald-50' : 'border-emerald-200 bg-emerald-50/30 hover:bg-emerald-50'">
                        
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <template x-if="!fileName">
                                <i class="fas fa-cloud-upload-alt text-4xl text-emerald-400 mb-3 group-hover:scale-110 transition-transform"></i>
                            </template>
                            <template x-if="fileName">
                                {{-- Ikon diganti jadi Excel --}}
                                <i class="fas fa-file-excel text-4xl text-emerald-600 mb-3 animate-bounce"></i>
                            </template>

                            <p class="text-xs font-bold text-emerald-700" x-text="fileName ? 'File Siap Diupload!' : 'Klik untuk Pilih File Excel'"></p>
                            
                            <p class="text-[10px] text-emerald-500 mt-2 font-mono truncate max-w-[250px]" x-show="fileName" x-text="fileName"></p>
                        </div>

                        {{-- Input accept diubah ke excel dan name diubah ke file_excel --}}
                        <input type="file" name="file_excel" class="hidden" required accept=".xlsx, .xls" 
                            @change="fileName = $event.target.files[0].name" />
                    </label>
                </div>
                
                <div class="flex gap-4 border-t border-gray-100 pt-6">
                    <button type="button" @click="modalImport = false; fileName = ''" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95"
                        :disabled="!fileName" :class="!fileName ? 'opacity-50 cursor-not-allowed' : ''">
                        Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div x-show="modalTambah" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalTambah = false" class="bg-white rounded-[35px] w-full max-w-lg p-8 shadow-2xl transform transition-all" x-transition.scale.origin.bottom>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight">Daftarkan Satker Baru</h3>
                <button @click="modalTambah = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form action="{{ route('admin.satker.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Kode Satker (6 Digit)</label>
                    <input type="text" name="kode_satker" maxlength="6" required placeholder="Contoh: 123456" 
                        class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 font-mono text-sm tracking-widest font-bold outline-none">
                </div>
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Nama Satuan Kerja</label>
                    <input type="text" name="nama_satker" required placeholder="Contoh: KOREM 143/HO" 
                        class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 uppercase text-sm font-bold outline-none">
                </div>

                @if(Auth::guard('admin')->user()->role === 'superadmin')
                <div class="mb-5 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <label class="block text-xs font-bold text-emerald-800 mb-2 uppercase tracking-wide">
                        <i class="fas fa-user-shield mr-1"></i> Penanggung Jawab SKPP (Super Admin)
                    </label>
                    <select name="admin_skpp_id" class="w-full bg-white border border-emerald-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-emerald-500 text-sm font-bold text-gray-700 outline-none">
                        <option value="">-- Bebas / Belum Ditentukan --</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="mb-8" x-data="{ show: false }">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Password Akun Satker</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required placeholder="Minimal 6 karakter" 
                            class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 pr-12 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm transition-all outline-none">
                        
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                            <i class="fas" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-4 border-t border-gray-100 pt-6">
                    <button type="button" @click="modalTambah = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/30 transition-all active:scale-95">Simpan Satker</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalEdit = false" class="bg-white rounded-[35px] w-full max-w-lg p-8 shadow-2xl transform transition-all" x-transition.scale.origin.bottom>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight">Edit Data Satker</h3>
                <button @click="modalEdit = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form :action="actionUrlEdit" method="POST">
                @csrf 
                @method('PUT')
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Kode Satker (6 Digit)</label>
                    <input type="text" name="kode_satker" :value="editData.kode_satker" maxlength="6" required 
                        class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 font-mono text-sm tracking-widest font-bold outline-none">
                </div>
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Nama Satuan Kerja</label>
                    <input type="text" name="nama_satker" :value="editData.nama_satker" required 
                        class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 uppercase text-sm font-bold outline-none">
                </div>

                @if(Auth::guard('admin')->user()->role === 'superadmin')
                <div class="mb-8 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <label class="block text-xs font-bold text-emerald-800 mb-2 uppercase tracking-wide">
                        <i class="fas fa-user-shield mr-1"></i> Penanggung Jawab SKPP (Super Admin)
                    </label>
                    <select name="admin_skpp_id" :value="editData.admin_skpp_id" class="w-full bg-white border border-emerald-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-emerald-500 text-sm font-bold text-gray-700 outline-none">
                        <option value="">-- Bebas / Belum Ditentukan --</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="mb-8 p-5 bg-blue-50/50 rounded-2xl border border-blue-100">
                    <label class="block text-[10px] font-bold text-[#1e3a8a] mb-1 uppercase tracking-widest">Petugas SKPP Saat Ini</label>
                    <p class="text-sm font-black text-blue-700 uppercase" x-text="editData.petugas_skpp ? editData.petugas_skpp.nama_lengkap : 'BELUM ADA PETUGAS'"></p>
                </div>
                @endif
                
                <div class="flex gap-4 border-t border-gray-100 pt-6">
                    <button type="button" @click="modalEdit = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/30 transition-all active:scale-95">Update Satker</button>
                </div>
            </form>
        </div>
    </div>

</div>
<script>
    document.getElementById('formImportSatker').addEventListener('submit', function() {
        Swal.fire({
            title: 'Sedang Mengimport Data...',
            html: 'Mohon tunggu, sistem sedang memproses file Excel kamu. <br> <b>Jangan menutup halaman ini!</b>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
</script>
@endsection