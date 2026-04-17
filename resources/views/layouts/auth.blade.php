<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BOKORI</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
</head>
<body class="flex h-screen w-full bg-white font-sans overflow-hidden">
    
    <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-10 bg-white z-10 relative">
        <div class="w-full max-w-md">
            
            <div class="mb-10 text-center md:text-left">
                <h1 class="text-4xl font-extrabold text-[#1e3a8a] tracking-tight mb-1">BOKORI</h1>
                <h2 class="text-xl font-bold text-[#1075BC] uppercase tracking-widest">KPPN KENDARI</h2>
                <p class="text-gray-500 mt-2 text-sm">Sistem Layanan Bantuan Operator</p>
            </div>

            @yield('content') 

        </div>
    </div>

    <div class="hidden md:flex w-1/2 bg-cover bg-center relative" 
         style="background-image: url('{{ asset('img/baground login.png') }}');">
        
        <div class="absolute inset-0 bg-gradient-to-t from-[#1e3a8a]/90 via-[#1e3a8a]/20 to-transparent"></div>
        
        <div class="absolute bottom-12 left-12 text-white z-10">
            <h2 class="text-4xl font-bold mb-3 tracking-tight">Inovasi Layanan</h2>
            <p class="text-lg text-white/90 max-w-md leading-relaxed">Cepat, Tepat, dan Akurat untuk Pelayanan KPPN yang Lebih Baik.</p>
        </div>
    </div>

</body>
</html>