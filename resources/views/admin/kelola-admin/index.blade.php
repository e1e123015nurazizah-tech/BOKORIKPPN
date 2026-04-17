@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20" x-data="{ modalTambah: false, modalEdit: false, modalImport: false, editData: {}, actionUrlEdit: '' }">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">Kelola Administrator</h1>
            <p class="text-gray-500 mt-1 text-sm italic">Master data akun pegawai KPPN pengelola sistem BOKORI.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            {{-- Tombol Export Excel --}}
            <a href="{{ route('admin.kelola-admin.export') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Download Excel
            </a>

            {{-- Tombol Import Excel --}}
            <button @click="modalImport = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-file-excel mr-2 text-lg"></i> Import Excel
            </button>

            <button @click="modalTambah = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center active:scale-95">
                <i class="fas fa-user-plus mr-2 text-lg"></i> Tambah Admin Baru
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
        
        {{-- FORM FILTER PENCARIAN ADMIN (AUTO-SUBMIT) --}}
        <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between border-b border-gray-100 pb-6">
            <form action="{{ route('admin.kelola-admin.index') }}" method="GET" class="w-full md:w-1/2 relative flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" id="searchAdminInput" value="{{ request('search') }}" 
                    @input.debounce.500ms="$event.target.form.submit()"
                    placeholder="Cari NIP atau Nama Admin..." 
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all shadow-sm">
            </form>
            
            @if(request('search'))
                <a href="{{ route('admin.kelola-admin.index') }}" class="text-sm text-red-500 hover:text-red-700 font-bold bg-red-50 py-2 px-4 rounded-lg transition-colors whitespace-nowrap">
                    <i class="fas fa-times mr-1"></i> Reset Pencarian
                </a>
            @endif
        </div>

        {{-- Script Fokus Kursor untuk Admin --}}
        @if(request('search'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let input = document.getElementById('searchAdminInput');
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
                        <th class="pb-5 px-4 font-black">Pegawai</th>
                        <th class="pb-5 px-4 font-black">Jabatan</th>
                        <th class="pb-5 px-4 font-black">Hak Akses</th>
                        <th class="pb-5 px-4 text-center font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($admins as $item)
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        <td class="py-4 px-4">
                            <div class="font-bold text-[#1e3a8a] uppercase">{{ $item->nama_lengkap }}</div>
                            <div class="text-xs text-gray-400 font-mono tracking-widest mt-1">NIP. {{ $item->nip }}</div>
                        </td>
                        <td class="py-4 px-4 text-gray-600 font-medium">{{ $item->jabatan }}</td>
                        <td class="py-4 px-4">
                            @if($item->role === 'superadmin')
                                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-purple-200">Super Admin</span>
                            @elseif($item->role === 'approver')
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-200">Approver</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border border-gray-200">Operator</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if(Auth::guard('admin')->id() !== $item->id)
                            <div class="flex items-center justify-center gap-2">
                                
                                <button @click="
                                    modalEdit = true;
                                    editData = {{ json_encode($item) }};
                                    actionUrlEdit = '{{ url('kelola-admin') }}/' + editData.id;
                                " class="text-blue-500 hover:text-blue-600 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors" title="Edit Data Admin">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('admin.kelola-admin.reset-password', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mereset password {{ $item->nama_lengkap }} ke bawaan pabrik (bokori123)?');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-amber-500 hover:text-amber-600 bg-amber-50 hover:bg-amber-100 p-2 rounded-lg transition-colors" title="Reset Password ke: bokori123">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.kelola-admin.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus Admin ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus Akun">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-gray-400 italic font-medium bg-gray-50 px-3 py-1 rounded-full">Akun Anda</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-300 italic font-medium">
                            @if(request('search'))
                                Tidak ada hasil untuk pencarian "{{ request('search') }}".
                            @else
                                Belum ada data Administrator lain.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $admins->links() }}</div>
    </div>

    {{-- MODAL IMPORT EXCEL --}}
    <div x-show="modalImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
        <div @click.away="modalImport = false" class="bg-white rounded-[35px] w-full max-w-md p-8 shadow-2xl transform transition-all" x-transition.scale.origin.center>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-emerald-700 uppercase tracking-tight">Import Admin Massal</h3>
                <button @click="modalImport = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <div class="mb-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                <p class="text-[11px] font-bold text-emerald-800 uppercase mb-2"><i class="fas fa-info-circle mr-1"></i> Format Kolom Excel (Baris 1):</p>
                <p class="text-[10px] text-emerald-600 leading-relaxed font-mono">
                    A: nip | B: nama_lengkap | C: jabatan | D: role | E: password
                </p>
                <p class="text-[9px] text-emerald-500 mt-1 italic">*Role: superadmin, approver, atau operator</p>
            </div>

            <form action="{{ route('admin.kelola-admin.import') }}" method="POST" enctype="multipart/form-data" x-data="{ fileName: '' }">
                @csrf
                <div class="mb-8 text-center">
                    <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-3xl cursor-pointer transition-all group"
                        :class="fileName ? 'border-emerald-500 bg-emerald-50' : 'border-emerald-200 bg-emerald-50/30 hover:bg-emerald-50'">
                        
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <template x-if="!fileName">
                                <i class="fas fa-cloud-upload-alt text-4xl text-emerald-400 mb-3 group-hover:scale-110 transition-transform"></i>
                            </template>
                            <template x-if="fileName">
                                <i class="fas fa-file-excel text-4xl text-emerald-600 mb-3 animate-bounce"></i>
                            </template>

                            <p class="text-xs font-bold text-emerald-700" x-text="fileName ? 'File Siap Diupload!' : 'Klik untuk Pilih File Excel'"></p>
                            
                            <p class="text-[10px] text-emerald-500 mt-2 font-mono truncate max-w-[250px]" x-show="fileName" x-text="fileName"></p>
                        </div>

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

    <div x-show="modalTambah" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div @click.away="modalTambah = false" class="bg-white rounded-[35px] w-full max-w-lg p-8 shadow-2xl transform transition-all" x-transition.scale.origin.bottom>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight">Tambah Admin Baru</h3>
                <button @click="modalTambah = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form action="{{ route('admin.kelola-admin.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">NIP Pegawai</label>
                        <input type="text" name="nip" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 font-mono text-sm tracking-wider">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Hak Akses</label>
                        <select name="role" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm font-bold text-gray-700 cursor-pointer">
                            <option value="operator">Operator (Biasa)</option>
                            <option value="approver">Approver (Penyetuju)</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 uppercase text-sm font-bold">
                </div>
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Jabatan</label>
                    <input type="text" name="jabatan" required placeholder="Contoh: Staff Verifikator" class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                
                <div class="mb-8" x-data="{ show: false }">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Password Sementara</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required placeholder="Minimal 6 karakter" 
                            class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 pr-12 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm transition-all">
                        
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                            <i class="fas" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-4 border-t border-gray-100 pt-5">
                    <button type="button" @click="modalTambah = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/30 transition-colors">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div @click.away="modalEdit = false" class="bg-white rounded-[35px] w-full max-w-lg p-8 shadow-2xl transform transition-all" x-transition.scale.origin.bottom>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-[#1e3a8a] uppercase tracking-tight">Edit Data Admin</h3>
                <button @click="modalEdit = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form :action="actionUrlEdit" method="POST">
                @csrf 
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">NIP Pegawai</label>
                        <input type="text" name="nip" :value="editData.nip" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 font-mono text-sm tracking-wider">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Hak Akses</label>
                        <select name="role" x-model="editData.role" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm font-bold text-gray-700 cursor-pointer">
                            <option value="operator">Operator (Biasa)</option>
                            <option value="approver">Approver (Penyetuju)</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-5">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" :value="editData.nama_lengkap" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 uppercase text-sm font-bold">
                </div>
                
                <div class="mb-8">
                    <label class="block text-xs font-bold text-[#1e3a8a] mb-2 uppercase tracking-wide">Jabatan</label>
                    <input type="text" name="jabatan" :value="editData.jabatan" required class="w-full bg-gray-50 border border-gray-200 py-3.5 px-4 rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                
                <div class="flex gap-4 border-t border-gray-100 pt-5">
                    <button type="button" @click="modalEdit = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/30 transition-colors">Update Data</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection