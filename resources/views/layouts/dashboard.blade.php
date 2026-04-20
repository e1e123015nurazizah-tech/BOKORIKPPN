@php
    // 1. KUNCI TAHUN AWAL (Tidak akan berubah selamanya)
    $tahunMulai = 2026; 

    // 2. AMBIL TAHUN KOMPUTER SAAT INI SECARA REAL-TIME
    $tahunSekarang = date('Y'); 
    
    // 3. BUAT RENTANG TAHUN OTOMATIS
    $daftarTahun = range($tahunMulai, $tahunSekarang); 
    
    // 4. AMBIL TAHUN DARI SESSION (Default: Tahun saat ini)
    $tahunAktif = session('tahun_aktif', date('Y')); 
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOKORI {{ $tahunAktif }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fbff] font-sans text-gray-800" x-data="{ sidebarOpen: false }">
    
    <div class="min-h-screen w-full relative">
        
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak 
             class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity">
        </div>

        <aside class="w-72 bg-[#42a5f5] text-white flex flex-col shadow-xl z-50 h-screen fixed top-0 left-0 transition-transform duration-300 ease-in-out md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <div class="p-6 md:p-8 flex items-center justify-between gap-3 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <i class="fas fa-building text-2xl md:text-3xl"></i> 
                    <h1 class="text-xl md:text-2xl font-black tracking-tight uppercase">
                        {{ Auth::guard('admin')->check() ? 'KPPN KENDARI' : 'SATKER' }}
                    </h1>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <nav class="flex-1 mt-2 md:mt-4 px-4 space-y-2 overflow-y-auto sidebar-scroll pb-10">
                @php
                    // Cek siapa yang login untuk menentukan link dan status aktif
                    $isUserAdmin = Auth::guard('admin')->check();
                    $targetRoute = $isUserAdmin ? route('admin.dashboard') : route('dashboard.satker');
                    $activeClass = $isUserAdmin ? '*dashboard-admin*' : '*dashboard-satker*';
                    // Tambahkan variabel role untuk pengecekan sidebar
                    $role = $isUserAdmin ? Auth::guard('admin')->user()->role : null;
                @endphp

                {{-- Hanya tampilkan Overview jika role BUKAN approver --}}
                @if($role !== 'approver')
                <a href="{{ $targetRoute }}" class="flex items-center py-3 md:py-4 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all {{ Request::is($activeClass) ? 'bg-white/20 font-bold shadow-inner' : '' }}">
                    <i class="fas fa-chart-line mr-4 text-xl"></i> 
                    <span class="text-sm md:text-base">Overview</span>
                </a>
                @endif

                @if(Auth::guard('satker')->check())                
                <div x-data="{ open: {{ Request::is('*pengajuan*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all group" :class="open ? 'bg-white/10' : ''">
                        <div class="flex items-center">
                            <i class="fas fa-file-signature mr-4 text-lg"></i>
                            <span class="text-sm font-semibold">Buat Pengajuan</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak x-collapse class="mt-2 space-y-1 pl-11 pr-4 relative">
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        <a href="{{ route('pengajuan.gajiweb') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*gajiweb*') ? 'bg-white/20 font-bold' : '' }}">
                            Rekon Gaji Web
                        </a>
                        <a href="{{ route('pengajuan.ppnpn') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*ppnpn*') ? 'bg-white/20 font-bold' : '' }}">
                            Rekon PPNPN
                        </a>
                        <a href="{{ route('pengajuan.skpp') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*skpp*') ? 'bg-white/20 font-bold' : '' }}">
                            Konfirmasi SKPP
                        </a>
                    </div>
                </div>

                <div x-data="{ openMonitoring: {{ Request::is('*monitoring*') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="openMonitoring = !openMonitoring" 
                            class="w-full flex items-center justify-between px-4 md:px-6 py-3 text-white font-medium rounded-xl hover:bg-white/10 transition-all group"
                            :class="openMonitoring ? 'bg-white/10' : ''">
                        <div class="flex items-center">
                            <i class="fas fa-desktop mr-4 text-lg text-white"></i>
                            <span class="text-sm font-semibold">Monitoring</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-white transition-transform duration-300" 
                           :class="openMonitoring ? 'transform rotate-180' : ''"></i>
                    </button>

                    <div x-show="openMonitoring" x-collapse x-cloak class="mt-2 space-y-1 pl-11 pr-4 relative">
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        <a href="{{ route('satker.monitoring.gajiweb') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.gajiweb') ? 'bg-white/20 font-bold' : '' }}">
                            Rekon Gaji Web
                        </a>
                        <a href="{{ route('satker.monitoring.ppnpn') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.ppnpn') ? 'bg-white/20 font-bold' : '' }}">
                            Rekon PPNPN
                        </a>
                        <a href="{{ route('satker.monitoring.skpp') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.skpp') ? 'bg-white/20 font-bold' : '' }}">
                            Monitoring SKPP
                        </a>
                    </div>
                </div>

                <a href="{{ route('satker.profil') }}" class="flex items-center mt-2 py-3 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('satker.profil') ? 'bg-white/20 font-bold shadow-inner' : '' }}">
                    <i class="fas fa-user-cog mr-4 text-lg"></i>
                    <span class="text-sm font-semibold">Profil Satker</span>
                </a>
                @endif

                @if(Auth::guard('admin')->check())
                <div class="px-4 md:px-6 pt-6 pb-2 text-[10px] font-bold text-white/50 uppercase tracking-widest">Administrator</div>
                
                {{-- Sembunyikan menu ini jika role adalah approver --}}
                @if($role !== 'approver')
                    @if($role === 'superadmin')
                    <a href="{{ route('admin.kelola-admin.index') }}" class="flex items-center py-3 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.kelola-admin.*') ? 'bg-white/20 font-bold shadow-inner' : '' }}">
                        <i class="fas fa-user-tie mr-4 text-lg"></i>
                        <span class="text-sm font-semibold">Kelola Admin</span>
                    </a>
                    @endif

                    <a href="{{ route('admin.satker.index') }}" class="flex items-center mt-2 py-3 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.satker.*') ? 'bg-white/20 font-bold shadow-inner' : '' }}">
                        <i class="fas fa-users mr-4 text-lg"></i>
                        <span class="text-sm font-semibold">Kelola Satker</span>
                    </a>

                    @if($role === 'superadmin')
                    <a href="{{ route('admin.maintenance.index') }}" class="flex items-center mt-2 py-3 px-4 md:px-6 hover:bg-red-500/20 rounded-xl transition-all {{ request()->routeIs('admin.maintenance.*') ? 'bg-red-600 font-bold shadow-inner' : '' }}">
                        <i class="fas fa-dumpster mr-4 text-lg {{ request()->routeIs('admin.maintenance.*') ? 'text-white' : 'text-red-400' }}"></i>
                        <span class="text-sm font-semibold text-white">Pembersihan Data</span>
                    </a>
                    @endif
                @endif

                <div x-data="{ openPengajuanAdmin: window.location.href.includes('pengajuan') }" class="mt-2">
                    <button @click="openPengajuanAdmin = !openPengajuanAdmin" 
                            class="w-full flex items-center justify-between px-4 md:px-6 py-3 text-white font-medium rounded-xl hover:bg-white/10 transition-all group"
                            :class="openPengajuanAdmin ? 'bg-white/10' : ''">
                        <div class="flex items-center">
                            <i class="fas fa-database mr-4 text-lg text-white"></i>
                            <span class="text-sm font-semibold">Data Pengajuan</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-white transition-transform duration-300" 
                        :class="openPengajuanAdmin ? 'transform rotate-180' : ''"></i>
                    </button>

                    <div x-show="openPengajuanAdmin" 
                        x-collapse 
                        x-cloak 
                        class="mt-2 space-y-1 pl-11 pr-4 relative">
                        
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        
                        {{-- Menu Semua Data: Tampil untuk semua (Admin & Approver) --}}
                        <a href="{{ route('admin.pengajuan.index') }}" 
                        class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ (Request::routeIs('admin.pengajuan.index') && !request('kategori')) ? 'bg-white/20 font-bold shadow-sm' : '' }}">
                            Semua Data
                        </a>

                        {{-- SUB-MENU INI DISEMBUNYIKAN DARI APPROVER --}}
                        @if($role !== 'approver')
                            <a href="{{ route('admin.pengajuan.index', ['kategori' => 'GajiWeb']) }}" 
                            class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'GajiWeb' ? 'bg-white/20 font-bold shadow-sm' : '' }}">
                                Rekon Gaji Web
                            </a>

                            <a href="{{ route('admin.pengajuan.index', ['kategori' => 'PPNPN']) }}" 
                            class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'PPNPN' ? 'bg-white/20 font-bold shadow-sm' : '' }}">
                                Rekon PPNPN
                            </a>
                        @endif

                        {{-- INI TETAP TAMPIL UNTUK SEMUA ROLE (Termasuk Approver) --}}
                        <a href="{{ route('admin.pengajuan.index', ['kategori' => 'SKPP']) }}" 
                        class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'SKPP' ? 'bg-white/20 font-bold shadow-sm' : '' }}">
                            Konfirmasi SKPP
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.profil') }}" class="flex items-center mt-2 py-3 px-4 md:px-6 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.profil') ? 'bg-white/20 font-bold shadow-inner' : '' }}">
                    <i class="fas fa-user-shield mr-4 text-lg"></i>
                    <span class="text-sm font-semibold">Profil Admin</span>
                </a>
                @endif

                <div class="pt-10">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center py-3 md:py-4 px-4 md:px-6 hover:bg-red-500 rounded-xl transition-all w-full text-left group">
                            <i class="fas fa-sign-out-alt mr-4 text-xl group-hover:animate-pulse"></i>
                            <span class="text-sm md:text-base">Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <div class="flex flex-col min-h-screen md:ml-72 transition-all duration-300">
            <header class="h-20 md:h-24 bg-white flex items-center justify-between px-4 md:px-12 shadow-md sticky top-0 z-30 flex-shrink-0">
                
                <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">
                    <button @click="sidebarOpen = true" class="md:hidden text-[#1e3a8a] hover:text-blue-500 focus:outline-none transition-colors">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                    
                    <h1 class="text-2xl md:text-5xl font-black text-blue-600 uppercase tracking-tighter">
                        BOKORI <span class="hidden sm:inline">{{ $tahunAktif }}</span>
                    </h1>
                </div>

                <div class="flex items-center gap-3 md:gap-6">
                    <div class="relative group z-50">
                        <button class="flex items-center gap-1 md:gap-2 bg-white text-[#1e3a8a] px-3 md:px-4 py-1.5 md:py-2 rounded-lg md:rounded-xl font-bold border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all shadow-sm text-xs md:text-sm">
                            <i class="fas fa-calendar-alt text-blue-500"></i> 
                            <span class="hidden sm:inline">Tahun:</span> {{ $tahunAktif }} 
                            <i class="fas fa-chevron-down text-[8px] md:text-[10px] ml-1 text-gray-400 transition-transform group-hover:rotate-180"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-28 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all overflow-hidden transform origin-top-right scale-95 group-hover:scale-100">
                            @foreach($daftarTahun as $thn)
                                <a href="{{ route('set.tahun', $thn) }}" 
                                class="block px-4 py-2.5 text-sm text-center text-gray-700 hover:bg-blue-50 hover:text-blue-700 font-bold border-b border-gray-50 last:border-0 {{ $tahunAktif == $thn ? 'bg-blue-600 text-white hover:bg-blue-700 hover:text-white' : '' }}">
                                    {{ $thn }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-6 md:h-10 w-[2px] bg-gray-100"></div>

                    @php
                        $isAdmin = Auth::guard('admin')->check() && !Auth::guard('satker')->check();
                        $user = $isAdmin ? Auth::guard('admin')->user() : Auth::guard('satker')->user();
                        $profileRoute = $isAdmin ? route('admin.profil') : route('satker.profil');
                        
                        if ($isAdmin) {
                            $name = $user->nama_lengkap;
                            
                            // PERBAIKAN: Gunakan route() untuk memanggil foto profil admin yang tersimpan di private
                            if ($user->foto_profil) {
                                $avatar = route('profil.foto', basename($user->foto_profil));
                            } else {
                                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=0D8ABC&color=fff";
                            }
                            
                            if ($user->role === 'superadmin') {
                                $roleLabel = 'SUPER ADMIN';
                            } elseif ($user->role === 'approver') {
                                $roleLabel = 'APPROVER';
                            } else {
                                $roleLabel = 'ADMIN';
                            }
                        } else {
                            $name = $user->nama_satker;
                            // Satker belum punya fitur upload foto, jadi pakai UI Avatars
                            $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=0D8ABC&color=fff";
                            $roleLabel = 'SATUAN KERJA';
                        }
                    @endphp

                    <a href="{{ $profileRoute }}" class="flex items-center gap-2 md:gap-4 text-right hover:bg-blue-50 p-1 md:p-2 md:pr-4 rounded-full transition-all cursor-pointer group border border-transparent hover:border-blue-100">
                        <div class="hidden sm:block">
                            <p class="text-[9px] md:text-[11px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1 group-hover:text-blue-500 transition-colors">
                                {{ $roleLabel }}
                            </p>
                            <p class="text-sm md:text-xl font-bold text-[#1e3a8a] leading-none">{{ Str::limit($name, 15) }}</p>
                            <p class="text-[8px] md:text-[10px] text-gray-400 mt-1 font-medium italic">
                                {{ $isAdmin ? 'NIP: ' . $user->nip : 'Kode: ' . $user->kode_satker }}
                            </p>
                        </div>
                        
                        <div class="w-10 h-10 md:w-14 md:h-14 rounded-full border-2 border-blue-100 shadow-sm overflow-hidden bg-blue-500 flex items-center justify-center group-hover:border-blue-400 group-hover:shadow-md transition-all group-hover:scale-105 flex-shrink-0">
                            <img src="{{ $avatar }}" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                    </a>
                </div>
            </header>

            <main class="px-4 py-6 md:px-12 md:py-10 flex-1 min-w-0 w-full max-w-full overflow-x-hidden">
                @yield('content')
            </main>
        </div>
        
    </div>

<script>
    // ============================================================
    // SENSOR GLOBAL: AUTO-REFRESH PINTAR (ANTI-REPUBLIK & ANTI-LAG)
    // ============================================================
    
    let waktuDiam = 0;
    const batasWaktuDiam = 60; // 60 detik diam = Refresh

    // Fungsi untuk mereset timer jika ada aktivitas
    function resetTimer() {
        waktuDiam = 0;
    }

    // Inisialisasi sensor saat halaman dimuat
    window.onload = function() {
        setInterval(hitungWaktu, 1000);

        // Pantau semua jenis pergerakan user
        document.onmousemove = resetTimer; 
        document.onclick = resetTimer;     
        document.onscroll = resetTimer;    
        document.onkeypress = resetTimer;  
        // Pantau juga sentuhan layar untuk perangkat mobile
        document.ontouchstart = resetTimer;
    };

    function hitungWaktu() {
        waktuDiam++; 
        
        if (waktuDiam >= batasWaktuDiam) {
            // 1. CEK MODAL: Jangan refresh kalau ada modal terbuka (role="dialog")
            const adaModalTerbuka = document.querySelector('[role="dialog"]') || document.querySelector('.modal-open');
            
            // 2. CEK FORM: Jangan refresh kalau di halaman tersebut ada form input (mencegah data hilang)
            const adaInputForm = document.querySelector('form');

            // EKSEKUSI: Refresh hanya jika tidak ada modal DAN tidak ada form input
            if (!adaModalTerbuka && !adaInputForm) {
                window.location.reload(); 
            } else {
                // Jika sedang isi form atau buka modal, reset timer ke 0 untuk menunggu 1 menit lagi
                resetTimer(); 
            }
        }
    }
</script>   
</body>
</html>