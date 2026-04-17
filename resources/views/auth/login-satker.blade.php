@extends('layouts.auth')

@section('content')
<form action="{{ route('login.post') }}" method="POST">
    @csrf
    
    @if($errors->any())
        <div class="bg-red-100 text-red-600 p-3 rounded-lg mb-6 text-sm font-semibold border border-red-200">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2 text-sm">Username</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="far fa-user"></i>
            </span>
            <input type="text" name="kode_satker" placeholder="Masukkan Kode Satker" required
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
        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20BOKORI" target="_blank" 
           class="text-sm font-semibold text-[#1075BC] hover:text-[#0c5c94] transition-colors">
            Lupa Password?
        </a>
    </div>

    <button type="submit" class="w-full bg-[#1075BC] hover:bg-[#0c5c94] text-white font-bold py-3.5 rounded-xl transition-all shadow-md active:scale-95 tracking-wider uppercase">
        MASUK
    </button>
</form>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            // Saat di-klik untuk dilihat: ubah jadi teks, ikon ganti mata terbuka
            passwordInput.type = 'text'; 
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye'); 
        } else {
            // Saat di-klik untuk disembunyikan: ubah jadi password, ikon ganti mata dicoret
            passwordInput.type = 'password'; 
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash'); 
        }
    }
</script>
@endsection