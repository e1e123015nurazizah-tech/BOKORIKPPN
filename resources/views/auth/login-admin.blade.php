@extends('layouts.auth')

@section('content')
<form action="{{ route('admin.login.post') }}" method="POST">
    @csrf
    
    @if($errors->any())
        <div class="bg-red-100 text-red-600 p-3 rounded-lg mb-6 text-sm font-semibold border border-red-200">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2 text-sm">NIP Pegawai</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="far fa-id-badge"></i>
            </span>
            <input type="text" name="nip" placeholder="Masukkan NIP Anda" required
                   class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:border-[#1075BC] focus:ring-1 focus:ring-[#1075BC] transition-all">
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2 text-sm">Password</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-lock"></i>
            </span>
            
            <input type="password" name="password" id="passwordInput" placeholder="••••••••" required
                   class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:border-[#1075BC] focus:ring-1 focus:ring-[#1075BC] transition-all">
            
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 cursor-pointer hover:text-[#1075BC] transition-colors" onclick="togglePassword()">
                <i class="far fa-eye-slash" id="toggleIcon"></i>
            </span>
        </div>
    </div>

    <div class="flex justify-end mb-8">
        <a href="#" onclick="alert('Silakan hubungi staf IT untuk mereset password Admin Anda.')" 
           class="text-sm font-semibold text-[#1075BC] hover:text-[#0c5c94] transition-colors">
            Lupa Password?
        </a>
    </div>

    <button type="submit" class="w-full bg-[#1075BC] hover:bg-[#0c5c94] text-white font-bold py-3.5 rounded-xl transition-all shadow-md active:scale-95 tracking-wider uppercase">
        LOGIN ADMIN
    </button>
</form>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            // PERBAIKAN 2: Saat mau dilihat, ubah jadi teks dan ikon mata terbuka
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            // Saat mau disembunyikan, ubah jadi password dan ikon mata dicoret
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }
</script>
@endsection