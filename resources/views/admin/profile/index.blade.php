@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20 max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-[#1e3a8a] tracking-tight uppercase">PROFIL ADMINISTRATOR</h1>
        <p class="text-gray-500 mt-2 text-lg italic">Perbarui informasi jabatan, foto profil, atau keamanan akun Anda.</p>
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
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100">
        <form action="{{ route('admin.profil.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h3 class="text-lg font-black text-gray-700 mb-6 border-b pb-2 uppercase tracking-wide flex items-center">
                <i class="fas fa-user-circle mr-2 text-blue-500"></i> Informasi Akun
            </h3>

            <div class="flex flex-col md:flex-row gap-10 mb-10">
                <div class="flex-shrink-0 flex flex-col items-center">
                    <label class="block text-sm font-bold text-[#1e3a8a] mb-4">Foto Profil Admin</label>
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-[2rem] overflow-hidden border-4 border-blue-50 shadow-lg relative bg-gray-100">
                            {{-- PERBAIKAN DI SINI: Hapus 'profile_admin/' dari pemanggilan asset() --}}
                            <img id="preview" 
                                 src="{{ Auth::guard('admin')->user()->foto_profil ? asset('storage/'.Auth::guard('admin')->user()->foto_profil) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::guard('admin')->user()->nama_lengkap).'&background=0D8ABC&color=fff&size=256' }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                <i class="fas fa-camera text-white text-3xl"></i>
                            </div>
                        </div>
                        
                        <input type="file" name="foto_profil" accept="image/*" 
                               class="absolute inset-0 opacity-0 cursor-pointer z-10"
                               onchange="previewImage(event)">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-3 font-medium italic">Klik foto untuk mengganti (Maks. 2MB)</p>

                    {{-- TOMBOL HAPUS FOTO SAJA (Form aslinya disembunyikan di bawah) --}}
                    @if(Auth::guard('admin')->user()->foto_profil)
                        <button type="submit" form="form-hapus-foto" onclick="return confirm('Yakin ingin menghapus foto profil ini secara permanen?');" 
                                class="mt-3 w-full text-[10px] font-black text-red-500 hover:text-white bg-red-50 hover:bg-red-500 py-2 rounded-xl transition-all uppercase tracking-widest border border-red-100 hover:border-red-500">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Foto
                        </button>
                    @endif
                </div>

                <div class="flex-1 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ Auth::guard('admin')->user()->nama_lengkap }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium uppercase">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" name="jabatan" value="{{ Auth::guard('admin')->user()->jabatan }}" required placeholder="Contoh: Kepala Seksi / Admin Sistem" 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-5 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-400 mb-2">NIP (ID Pegawai)</label>
                        <input type="text" value="{{ Auth::guard('admin')->user()->nip }}" disabled 
                               class="w-full bg-gray-100 border border-gray-200 text-gray-500 py-3.5 px-5 rounded-2xl cursor-not-allowed font-bold tracking-widest">
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-black text-gray-700 mb-4 border-b pb-2 uppercase tracking-wide flex items-center">
                <i class="fas fa-lock mr-2 text-blue-500"></i> Ganti Password
            </h3>
            <p class="text-xs text-gray-400 mb-6 font-medium italic">*Kosongkan bagian ini jika Anda tidak ingin mengganti password.</p>

            <div class="mb-6" x-data="{ show: false }">
                <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Password Lama</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password_lama" placeholder="Masukkan password saat ini" 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-5 pr-12 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                        <i class="fas" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                    </button>
                </div>
            </div>

            <div x-data="{ 
                pass: '', 
                show: false
            }" class="mb-8">
                <div class="flex flex-col md:flex-row gap-6 mb-4">
                    
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Password Baru</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_baru" x-model="pass" placeholder="Masukkan password baru" 
                                   class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-5 pr-12 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                                <i class="fas" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </button>
                        </div>

                        <div class="mt-3 px-2">
                            <ul class="space-y-1.5">
                                <li class="flex items-center gap-2 text-xs transition-colors" :class="pass.length >= 8 ? 'text-gray-800 font-bold' : 'text-gray-400 font-medium'">
                                    <i class="fas" :class="pass.length >= 8 ? 'fa-check text-[#1e3a8a]' : 'fa-circle text-[6px]'"></i> Minimal 8 Karakter
                                </li>
                                <li class="flex items-center gap-2 text-xs transition-colors" :class="/[A-Z]/.test(pass) ? 'text-gray-800 font-bold' : 'text-gray-400 font-medium'">
                                    <i class="fas" :class="/[A-Z]/.test(pass) ? 'fa-check text-[#1e3a8a]' : 'fa-circle text-[6px]'"></i> Harus Mengandung Huruf Besar (A-Z)
                                </li>
                                <li class="flex items-center gap-2 text-xs transition-colors" :class="/[0-9]/.test(pass) ? 'text-gray-800 font-bold' : 'text-gray-400 font-medium'">
                                    <i class="fas" :class="/[0-9]/.test(pass) ? 'fa-check text-[#1e3a8a]' : 'fa-circle text-[6px]'"></i> Harus Mengandung Angka (0-9)
                                </li>
                                <li class="flex items-center gap-2 text-xs transition-colors" :class="/[^A-Za-z0-9]/.test(pass) ? 'text-gray-800 font-bold' : 'text-gray-400 font-medium'">
                                    <i class="fas" :class="/[^A-Za-z0-9]/.test(pass) ? 'fa-check text-[#1e3a8a]' : 'fa-circle text-[6px]'"></i> Harus Mengandung Simbol (@#$%)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex-1" x-data="{ show: false }">
                        <label class="block text-sm font-bold text-[#1e3a8a] mb-2">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_baru_confirmation" placeholder="Ketik ulang password baru" 
                                   class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-5 pr-12 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-blue-600 focus:outline-none transition-colors">
                                <i class="fas" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-10 rounded-2xl transition-all shadow-lg shadow-blue-500/30 active:scale-95">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>

        {{-- FORM TERSEMBUNYI UNTUK MENGHAPUS FOTO (Supaya tidak tumpang tindih) --}}
        @if(Auth::guard('admin')->user()->foto_profil)
            <form id="form-hapus-foto" action="{{ route('admin.profil.hapus_foto') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>

<script>
    // Script khusus untuk Preview Foto Admin
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection