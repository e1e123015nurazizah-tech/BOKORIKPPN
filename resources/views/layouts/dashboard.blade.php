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
<body class="bg-[#f8fbff] font-sans text-gray-800" x-data="{ sidebarOpen: window.innerWidth >= 768 }" @resize.window="sidebarOpen = window.innerWidth >= 768">
    
    <div class="min-h-screen w-full relative">
        
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak 
             class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity">
        </div>

        <aside class="bg-[#42a5f5] text-white flex flex-col shadow-xl z-50 h-screen fixed top-0 left-0 transition-all duration-300 ease-in-out overflow-x-hidden"
               :class="sidebarOpen ? 'w-72 translate-x-0' : 'w-20 -translate-x-full md:translate-x-0'">
            
            <div class="p-6 md:p-8 flex items-center justify-between gap-3 flex-shrink-0" :class="!sidebarOpen ? 'justify-center md:px-0' : ''">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden md:block text-white hover:text-blue-200 focus:outline-none transition-transform hover:scale-110 flex-shrink-0" title="Buka/Tutup Sidebar">
                        <i class="fas fa-bars text-2xl md:text-3xl text-center"></i>
                    </button>
                    
                    <h1 x-show="sidebarOpen" x-transition.opacity class="text-xl md:text-2xl font-black tracking-tight uppercase whitespace-nowrap">
                        {{ Auth::guard('admin')->check() ? 'KPPN KENDARI' : 'SATKER' }}
                    </h1>
                </div>
                
                <button @click="sidebarOpen = false" class="md:hidden text-white hover:text-gray-200 transition-transform hover:scale-110 flex-shrink-0" title="Tutup Sidebar">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <nav class="flex-1 mt-2 md:mt-4 px-4 space-y-2 overflow-y-auto sidebar-scroll pb-10">
                @php
                    $isUserAdmin = Auth::guard('admin')->check();
                    $targetRoute = $isUserAdmin ? route('admin.dashboard') : route('dashboard.satker');
                    $activeClass = $isUserAdmin ? '*dashboard-admin*' : '*dashboard-satker*';
                    $role = $isUserAdmin ? Auth::guard('admin')->user()->role : null;
                @endphp

                @if($role !== 'approver')
                <a href="{{ $targetRoute }}" class="flex items-center py-3 md:py-4 px-4 hover:bg-white/10 rounded-xl transition-all {{ Request::is($activeClass) ? 'bg-white/20 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                    <i class="fas fa-chart-line text-xl w-6 text-center flex-shrink-0"></i> 
                    <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm md:text-base whitespace-nowrap">Overview</span>
                </a>
                @endif

                @if(Auth::guard('satker')->check())                
                <div x-data="{ open: {{ Request::is('*pengajuan*') ? 'true' : 'false' }} }">
                    <button @click="if(sidebarOpen) { open = !open } else { sidebarOpen = true; open = true }" 
                            class="w-full flex items-center justify-between py-3 px-4 hover:bg-white/10 rounded-xl transition-all group" 
                            :class="[open && sidebarOpen ? 'bg-white/10' : '', !sidebarOpen ? 'justify-center' : 'md:px-6']">
                        <div class="flex items-center">
                            <i class="fas fa-file-signature text-lg w-6 text-center flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Buat Pengajuan</span>
                        </div>
                        <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform duration-300 flex-shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open && sidebarOpen" x-cloak x-collapse class="mt-2 space-y-1 pl-11 pr-4 relative">
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        <a href="{{ route('pengajuan.gajiweb') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*gajiweb*') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Rekon Gaji Web
                        </a>
                        <a href="{{ route('pengajuan.ppnpn') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*ppnpn*') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Rekon PPNPN
                        </a>
                        <a href="{{ route('pengajuan.skpp') }}" class="block px-4 py-2.5 hover:bg-white/20 rounded-lg text-sm font-medium transition-all {{ Request::is('*skpp*') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Konfirmasi SKPP
                        </a>
                    </div>
                </div>

                <div x-data="{ openMonitoring: {{ Request::is('*monitoring*') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="if(sidebarOpen) { openMonitoring = !openMonitoring } else { sidebarOpen = true; openMonitoring = true }" 
                            class="w-full flex items-center justify-between px-4 py-3 text-white font-medium rounded-xl hover:bg-white/10 transition-all group"
                            :class="[openMonitoring && sidebarOpen ? 'bg-white/10' : '', !sidebarOpen ? 'justify-center' : 'md:px-6']">
                        <div class="flex items-center">
                            <i class="fas fa-desktop text-lg w-6 text-center text-white flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Monitoring</span>
                        </div>
                        <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs text-white transition-transform duration-300 flex-shrink-0" :class="openMonitoring ? 'transform rotate-180' : ''"></i>
                    </button>

                    <div x-show="openMonitoring && sidebarOpen" x-collapse x-cloak class="mt-2 space-y-1 pl-11 pr-4 relative">
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        <a href="{{ route('satker.monitoring.gajiweb') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.gajiweb') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Rekon Gaji Web
                        </a>
                        <a href="{{ route('satker.monitoring.ppnpn') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.ppnpn') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Rekon PPNPN
                        </a>
                        <a href="{{ route('satker.monitoring.skpp') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request()->routeIs('satker.monitoring.skpp') ? 'bg-white/20 font-bold' : '' }} whitespace-nowrap">
                            Monitoring SKPP
                        </a>
                    </div>
                </div>

                <a href="{{ route('satker.profil') }}" class="flex items-center mt-2 py-3 px-4 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('satker.profil') ? 'bg-white/20 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                    <i class="fas fa-user-cog text-lg w-6 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Profil Satker</span>
                </a>
                @endif

                @if(Auth::guard('admin')->check())
                <div x-show="sidebarOpen" x-transition.opacity class="px-4 md:px-6 pt-6 pb-2 text-[10px] font-bold text-white/50 uppercase tracking-widest whitespace-nowrap">Administrator</div>
                
                @if($role !== 'approver')
                    @if($role === 'superadmin')
                    <a href="{{ route('admin.kelola-admin.index') }}" class="flex items-center py-3 px-4 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.kelola-admin.*') ? 'bg-white/20 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                        <i class="fas fa-user-tie text-lg w-6 text-center flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Kelola Admin</span>
                    </a>
                    @endif

                    <a href="{{ route('admin.satker.index') }}" class="flex items-center mt-2 py-3 px-4 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.satker.*') ? 'bg-white/20 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                        <i class="fas fa-users text-lg w-6 text-center flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Kelola Satker</span>
                    </a>

                    @if($role === 'superadmin')
                    <a href="{{ route('admin.maintenance.index') }}" class="flex items-center mt-2 py-3 px-4 hover:bg-red-500/20 rounded-xl transition-all {{ request()->routeIs('admin.maintenance.*') ? 'bg-red-600 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                        <i class="fas fa-dumpster text-lg w-6 text-center flex-shrink-0 {{ request()->routeIs('admin.maintenance.*') ? 'text-white' : 'text-red-400' }}"></i>
                        <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold text-white whitespace-nowrap">Pembersihan Data</span>
                    </a>
                    @endif
                @endif

                <div x-data="{ openPengajuanAdmin: window.location.href.includes('pengajuan') }" class="mt-2">
                    <button @click="if(sidebarOpen) { openPengajuanAdmin = !openPengajuanAdmin } else { sidebarOpen = true; openPengajuanAdmin = true }" 
                            class="w-full flex items-center justify-between px-4 py-3 text-white font-medium rounded-xl hover:bg-white/10 transition-all group"
                            :class="[openPengajuanAdmin && sidebarOpen ? 'bg-white/10' : '', !sidebarOpen ? 'justify-center' : 'md:px-6']">
                        <div class="flex items-center">
                            <i class="fas fa-database text-lg w-6 text-center text-white flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Data Pengajuan</span>
                        </div>
                        <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs text-white transition-transform duration-300 flex-shrink-0" :class="openPengajuanAdmin ? 'transform rotate-180' : ''"></i>
                    </button>

                    <div x-show="openPengajuanAdmin && sidebarOpen" x-collapse x-cloak class="mt-2 space-y-1 pl-11 pr-4 relative">
                        <div class="absolute left-7 top-0 bottom-2 w-px bg-white/30"></div>
                        <a href="{{ route('admin.pengajuan.index') }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ (Request::routeIs('admin.pengajuan.index') && !request('kategori')) ? 'bg-white/20 font-bold shadow-sm' : '' }} whitespace-nowrap">
                            Semua Data
                        </a>

                        @if($role !== 'approver')
                            <a href="{{ route('admin.pengajuan.index', ['kategori' => 'GajiWeb']) }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'GajiWeb' ? 'bg-white/20 font-bold shadow-sm' : '' }} whitespace-nowrap">
                                Rekon Gaji Web
                            </a>
                            <a href="{{ route('admin.pengajuan.index', ['kategori' => 'PPNPN']) }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'PPNPN' ? 'bg-white/20 font-bold shadow-sm' : '' }} whitespace-nowrap">
                                Rekon PPNPN
                            </a>
                        @endif

                        <a href="{{ route('admin.pengajuan.index', ['kategori' => 'SKPP']) }}" class="block px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:bg-white/20 transition-colors {{ request('kategori') == 'SKPP' ? 'bg-white/20 font-bold shadow-sm' : '' }} whitespace-nowrap">
                            Konfirmasi SKPP
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.profil') }}" class="flex items-center mt-2 py-3 px-4 hover:bg-white/10 rounded-xl transition-all {{ request()->routeIs('admin.profil') ? 'bg-white/20 font-bold shadow-inner' : '' }}" :class="!sidebarOpen ? 'justify-center' : 'md:px-6'">
                    <i class="fas fa-user-shield text-lg w-6 text-center flex-shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm font-semibold whitespace-nowrap">Profil Admin</span>
                </a>
                @endif

                <div class="pt-10">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center py-3 md:py-4 px-4 hover:bg-red-500 rounded-xl transition-all w-full group" :class="!sidebarOpen ? 'justify-center' : 'md:px-6 text-left'">
                            <i class="fas fa-sign-out-alt text-xl w-6 text-center flex-shrink-0 group-hover:animate-pulse"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="ml-4 text-sm md:text-base whitespace-nowrap">Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <div class="flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'md:ml-72' : 'md:ml-20'">
            <header class="h-20 md:h-24 bg-white flex items-center justify-between px-4 md:px-12 shadow-md sticky top-0 z-30 flex-shrink-0">
                
                <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-[#1e3a8a] hover:text-blue-500 focus:outline-none transition-colors">
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
    let waktuDiam = 0;
    const batasWaktuDiam = 60; 

    function resetTimer() {
        waktuDiam = 0;
    }

    window.onload = function() {
        setInterval(hitungWaktu, 1000);

        document.onmousemove = resetTimer; 
        document.onclick = resetTimer;     
        document.onscroll = resetTimer;    
        document.onkeypress = resetTimer;  
        document.ontouchstart = resetTimer;
    };

    function hitungWaktu() {
        waktuDiam++; 
        
        if (waktuDiam >= batasWaktuDiam) {
            const adaModalTerbuka = document.querySelector('[role="dialog"]') || document.querySelector('.modal-open');
            const adaInputForm = document.querySelector('form');

            if (!adaModalTerbuka && !adaInputForm) {
                window.location.reload(); 
            } else {
                resetTimer(); 
            }
        }
    }
</script>   
</body>
</html>