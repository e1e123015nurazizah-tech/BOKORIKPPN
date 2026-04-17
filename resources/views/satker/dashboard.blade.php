@extends('layouts.dashboard')

@section('content')
<div class="mt-4 pb-20">
    <h2 class="text-4xl font-bold text-gray-800 tracking-tight text-[#1e3a8a]">Overview {{ Auth::guard('satker')->user()->nama_satker }}</h2>
    <p class="text-gray-500 mt-2 text-lg italic">Data layanan BOKORI tahun {{ $tahunAktif }}</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
        <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100 text-center transform transition hover:-translate-y-2 hover:shadow-xl group">
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-4 tracking-widest group-hover:text-blue-500 transition-colors">Volume Rekon Gajiweb</p>
            <p class="text-6xl font-black text-[#1e3a8a]">{{ number_format($volumeGaji, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100 text-center transform transition hover:-translate-y-2 hover:shadow-xl group">
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-4 tracking-widest group-hover:text-blue-500 transition-colors">Volume Rekon PPNPN</p>
            <p class="text-6xl font-black text-[#1e3a8a]">{{ number_format($volumePPNPN, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-100 text-center transform transition hover:-translate-y-2 hover:shadow-xl group">
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-4 tracking-widest group-hover:text-blue-500 transition-colors">Volume SKPP</p>
            <p class="text-6xl font-black text-[#1e3a8a]">{{ number_format($volumeSKPP, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-10">
        <div class="lg:col-span-2 bg-white p-8 rounded-[35px] shadow-sm border border-gray-50">
            <h3 class="font-bold text-[#1e3a8a] mb-6 uppercase tracking-widest text-[10px]">Tren Aktivitas Bulanan {{ $tahunAktif }}</h3>
            <div class="relative h-72 w-full"><canvas id="lineChart"></canvas></div>
        </div>

        <div class="bg-white p-8 rounded-[35px] shadow-sm border border-gray-50 flex flex-col items-center justify-center">
            <h3 class="font-bold text-[#1e3a8a] mb-6 uppercase tracking-widest text-[10px] text-center">Persentase Status Global</h3>
            <div class="relative h-64 w-full">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 text-center">
                <p class="text-3xl font-black text-[#1e3a8a]">{{ $statusSelesai + $statusDiproses + $statusMenunggu + $statusDitolak }}</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total Pengajuan</p>
            </div>
        </div>
    </div>

    <h3 class="text-xl font-bold text-[#1e3a8a] mt-16 mb-6 uppercase tracking-tighter">Proporsi Pengajuan Berdasarkan Kategori</h3>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 flex flex-col items-center hover:shadow-md transition-all">
            <h4 class="font-bold text-gray-700 mb-6 uppercase text-xs tracking-widest text-center">Layanan REKON GAJI</h4>
            <div class="relative h-64 w-full"><canvas id="chartGaji"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 flex flex-col items-center hover:shadow-md transition-all">
            <h4 class="font-bold text-gray-700 mb-6 uppercase text-xs tracking-widest text-center">Layanan PPNPN</h4>
            <div class="relative h-64 w-full"><canvas id="chartPpnpn"></canvas></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 flex flex-col items-center hover:shadow-md transition-all">
            <h4 class="font-bold text-gray-700 mb-6 uppercase text-xs tracking-widest text-center">Layanan SKPP</h4>
            <div class="relative h-64 w-full"><canvas id="chartSkpp"></canvas></div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const barOptions = { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { legend: { display: false } },
        scales: { 
            y: { beginAtZero: true, min: 0, grid: { display: false } }, 
            x: { grid: { display: false } } 
        }
    };

    // 1. Donut Chart Status (Sudah Diperbarui dengan Persentase)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        plugins: [ChartDataLabels], // Aktifkan plugin datalabels HANYA di chart ini
        data: {
            labels: ['Selesai', 'Diproses', 'Menunggu', 'Ditolak'],
            datasets: [{
                data: [{{ $statusSelesai }}, {{ $statusDiproses }}, {{ $statusMenunggu }}, {{ $statusDitolak }}],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: { 
            cutout: '70%', // Diperkecil sedikit (dari 80%) agar ada ruang untuk teks persentase
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { usePointStyle: true, padding: 15 } 
                },
                // Pengaturan gaya dan teks persentasenya
                datalabels: {
                    color: '#ffffff', // Warna teks putih
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: (value, context) => {
                        // Jangan tampilkan angka jika datanya 0
                        if (value === 0) return ''; 

                        // Hitung total semua data
                        let dataArr = context.chart.data.datasets[0].data;
                        let total = dataArr.reduce((a, b) => a + b, 0);
                        
                        // Jadikan persentase (bulat tanpa koma)
                        let percentage = Math.round((value / total) * 100) + '%';
                        return percentage;
                    }
                }
            } 
        }
    });

    // Plugin Kustom: Membuat Garis Vertikal Putus-putus (Crosshair) saat Hover
    const hoverLinePlugin = {
        id: 'hoverLine',
        afterDraw: chart => {
            if (chart.tooltip?._active?.length) {
                const x = chart.tooltip._active[0].element.x;
                const yAxis = chart.scales.y;
                const ctx = chart.ctx;

                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, yAxis.top);
                ctx.lineTo(x, yAxis.bottom);
                ctx.lineWidth = 1.5;
                ctx.strokeStyle = '#64748b'; // Warna abu-abu elegan
                ctx.setLineDash([5, 5]); // Efek putus-putus
                ctx.stroke();
                ctx.restore();
            }
        }
    };

    // 2. Line Chart Tren Bulanan (Multi-Line & Index Tooltip & Hover Line)
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        plugins: [hoverLinePlugin], // <-- Daftarkan plugin kustom di sini
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                { 
                    label: 'PNS', 
                    data: @json($trenPNS), 
                    borderColor: '#1e3a8a', 
                    backgroundColor: '#1e3a8a',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4 
                },
                { 
                    label: 'PPPK', 
                    data: @json($trenPPPK), 
                    borderColor: '#fbbf24', 
                    backgroundColor: '#fbbf24',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4 
                },
                { 
                    label: 'POLRI', 
                    data: @json($trenPOLRI), 
                    borderColor: '#78350f', 
                    backgroundColor: '#78350f',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4 
                },
                { 
                    label: 'TNI', 
                    data: @json($trenTNI), 
                    borderColor: '#16a34a', 
                    backgroundColor: '#16a34a',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4 
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    min: 0,
                    ticks: {
                        stepSize: 1 // Supaya sumbu Y angkanya tidak koma-koma (0, 1, 2, dst)
                    }
                },
                x: {
                    grid: {
                        drawOnChartArea: true,
                        color: 'rgba(0,0,0,0.03)' // Garis vertikal bawaan disamarkan
                    }
                }
            },
            plugins: { 
                legend: { 
                    display: true,
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 8 }
                },
                datalabels: { display: false }, // Mematikan angka statis dari plugin donat tadi
                tooltip: {
                    usePointStyle: true,
                    boxPadding: 6,
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    backgroundColor: 'rgba(15, 23, 42, 0.9)', 
                    titleColor: '#ffffff',
                    bodyColor: '#e2e8f0',
                    borderColor: '#334155',
                    borderWidth: 1
                }
            }
        }
    });

    // 3. Chart Gaji
    new Chart(document.getElementById('chartGaji'), {
        type: 'bar',
        data: {
            labels: ['PNS', 'PPPK', 'POLRI', 'TNI'],
            datasets: [{ 
                data: [{{ $gajiPNS }}, {{ $gajiPPPK }}, {{ $gajiPOLRI }}, {{ $gajiTNI }}], 
                backgroundColor: ['#1e3a8a', '#fbbf24', '#78350f', '#16a34a'], 
                borderRadius: 8 
            }]
        },
        options: barOptions
    });

    // 4. Chart PPNPN
    new Chart(document.getElementById('chartPpnpn'), {
        type: 'bar',
        data: {
            labels: ['Baru', 'Batal'],
            datasets: [{ 
                data: [{{ $ppnpnBaru }}, {{ $ppnpnBatal }}], 
                backgroundColor: ['#3b82f6', '#f50b42'], 
                borderRadius: 8 
            }]
        },
        options: barOptions
    });

    // 5. Chart SKPP
    new Chart(document.getElementById('chartSkpp'), {
        type: 'bar',
        data: {
            labels: ['PNS', 'TNI', 'POLRI', 'PPPK'],
            datasets: [{ 
                data: [{{ $skppPNS }}, {{ $skppTNI }}, {{ $skppPOLRI }}, {{ $skppPPPK }}], 
                backgroundColor: ['#1e3a8a', '#16a34a', '#78350f', '#fbbf24'], 
                borderRadius: 8 
            }]
        },
        options: barOptions
    });
</script>
@endsection