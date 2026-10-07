<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SINTUVU | Sistem Informasi dan Data Konservasi Terpadu Sulawesi Tengah - Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            600: '#15803d',
                            700: '#166534',
                            800: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js untuk Grafik Dinamis -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100/90 font-sans text-slate-800 antialiased min-h-screen relative">

    <!-- BACKGROUND GLOBAL KAWASAN KONSERVASI DENGAN OPASITAS TIPIS -->
    <div class="fixed inset-0 pointer-events-none z-[-1] overflow-hidden">
        <img src="https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1920&auto=format&fit=crop" alt="Background Konservasi" class="w-full h-full object-cover opacity-15">
        <div class="absolute inset-0 bg-slate-100/75 backdrop-blur-[2px]"></div>
    </div>

    <!-- NAVBAR UTAMA -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo & Title Instansi -->
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-xl bg-forest-700/10 border border-forest-700/20 flex items-center justify-center p-1.5 shadow-xs overflow-hidden">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Logo SINTUVU" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://via.placeholder.com/50?text=SINTUVU';">
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm tracking-tight text-slate-900 leading-tight">SINTUVU</span>
                    <span class="text-[11px] text-slate-500 font-medium">BKSDA Sulawesi Tengah</span>
                </div>
            </div>

            <!-- Menu Navigasi -->
            <nav class="hidden md:flex items-center space-x-1 bg-slate-100/80 p-1.5 rounded-xl border border-slate-200">
                <a href="{{ route('konservasi.dashboard') }}" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white text-forest-700 shadow-xs">Dashboard</a>
                <a href="{{ route('konservasi.index') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Rekapitulasi</a>
                <a href="{{ route('konservasi.create') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Tambah Data</a>
                <a href="{{ route('konservasi.peta') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">GIS</a>
            </nav>

            <!-- Status & User Profile (DINAMIS SESUAI USER LOGIN) -->
            <div class="flex items-center space-x-4">
                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Sistem Aktif
                </span>
                <div class="flex items-center space-x-2.5 border-l pl-4 border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-forest-700 text-white flex items-center justify-center font-bold text-xs shadow-sm uppercase">
                        {{ strtoupper(substr(Auth::user()->name ?? 'User', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                        <p class="text-[10px] text-slate-500">{{ Auth::user()->role ?? 'Operator' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="Keluar dari akun" aria-label="Keluar dari akun" class="inline-flex h-9 w-9 xl:w-auto items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white px-2.5 text-rose-700 transition hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                        <span class="hidden xl:inline text-xs font-semibold">Keluar</span>
                    </button>
                </form>
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- HERO BANNER -->
        <div class="relative rounded-2xl overflow-hidden shadow-xl bg-slate-900 text-white min-h-[240px] flex flex-col justify-between p-8 border border-slate-800">
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('images/satwa-endemik-sulteng.jpg') }}" alt="Satwa Endemik & Kawasan Konservasi Sulawesi Tengah" class="w-full h-full object-cover opacity-50 transform hover:scale-105 transition duration-700" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1600&auto=format&fit=crop'">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-900/80 to-slate-950/40"></div>
            </div>

            <!-- Konten Banner -->
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-medium mb-3 text-emerald-300 shadow-sm">
                    <i class="fa-solid fa-tree"></i> <span>Kawasan Konservasi & Satwa Endemik Sulawesi Tengah</span>
                </div>
                
                <h2 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Selamat Datang, {{ Auth::user()->name ?? 'Petugas Operator' }}!
                </h2>

                <p class="mt-2 text-xs sm:text-sm text-slate-200 leading-relaxed">
                    Pusat pemantauan data kawasan hutan, perlindungan satwa endemik, serta pemetaan koordinat GIS wilayah kerja BKSDA Sulawesi Tengah.
                </p>
            </div>

            <!-- Tombol Tambah Data & Badge Hak Akses -->
            <div class="relative z-10 flex flex-wrap items-center justify-between gap-4 mt-6 pt-4 border-t border-white/15">
                <div class="flex items-center space-x-2 text-xs text-slate-200">
                    <i class="fa-solid fa-shield-halved text-amber-400"></i>
                    <span>Hak Akses: <strong class="text-white font-semibold">Petugas {{ Auth::user()->role ?? 'Operator' }} Terverifikasi</strong></span>
                </div>
                <div>
                    <a href="{{ route('konservasi.create') }}" class="inline-flex items-center justify-center px-4.5 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 shadow-md transition">
                        <i class="fa-solid fa-plus mr-1.5"></i> Tambah Data Konservasi
                    </a>
                </div>
            </div>
        </div>

        <!-- GRID STATISTIK DINAMIS DARI CONTROLLER -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: Total Entri -->
            <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
                <div class="flex items-center justify-between text-slate-500 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Entri Data</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <i class="fa-solid fa-database text-lg"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalData ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-2 flex items-center">
                    Seluruh data konservasi yang tersimpan
                </p>
            </div>

            <!-- Card 2: Cakupan Sub-Bidang -->
            <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
                <div class="flex items-center justify-between text-slate-500 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Sub-Bidang Terisi</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <i class="fa-solid fa-tree text-lg"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-900">{{ $totalSubBidangTerisi ?? 0 }} <span class="text-lg text-slate-400">/ {{ $totalSubBidang ?? 0 }}</span></h3>
                <p class="text-xs text-slate-500 mt-2">Kategori yang memiliki entri dari enam bidang</p>
            </div>

            <!-- Card 3: Data Volume -->
            <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 hover:shadow-md transition">
                <div class="flex items-center justify-between text-slate-500 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Entri dengan Nilai</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <i class="fa-solid fa-chart-column text-lg"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalEntriDenganNilai ?? 0) }}</h3>
                <p class="text-xs text-slate-500 mt-2">Input yang memiliki nilai kuantitatif</p>
            </div>

        </div>

        <!-- SECTION BAWAH: GRAFIK & AKSES PETA GIS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Grafik jumlah entri per enam bidang -->
            <div class="lg:col-span-2 bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-700 mb-1">
                            <i class="fa-solid fa-chart-column text-sm"></i>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider">Perbandingan Entri</span>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Jumlah Entri per Sub-Bidang</h3>
                        <p class="text-xs text-slate-500 mt-1">Setiap sub-bidang ditampilkan terpisah dan diberi warna sesuai bidang induknya.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-600">
                        {{ count($subBidangChart) }} sub-bidang memiliki entri
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(260px,0.8fr)] gap-6 items-start">
                    <div class="max-h-[360px] min-w-0 overflow-y-auto pr-2">
                        @if(count($subBidangChart))
                            <div style="height: {{ max(240, count($subBidangChart) * 34) }}px">
                                <canvas id="subBidangChart" aria-label="Jumlah entri per sub-bidang konservasi" role="img"></canvas>
                            </div>
                        @else
                            <div class="flex h-60 items-center justify-center rounded-xl border border-dashed border-slate-200 text-xs text-slate-500">Belum ada entri pada sub-bidang.</div>
                        @endif
                    </div>

                    <div class="lg:border-l lg:border-slate-100 lg:pl-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Rincian Sub-Bidang</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">Jumlah entri di tiap kategori</p>
                            </div>
                        </div>
                        <div class="max-h-[360px] space-y-3 overflow-y-auto pr-2">
                            @foreach($subBidangChart as $subBidang)
                                @php
                                    $parentBidang = collect($bidangChart)->first(fn ($bidang) => str_starts_with($subBidang['kode'], $bidang['kode'] . '.'));
                                @endphp
                                <div class="flex items-start justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2">
                                    <div class="flex min-w-0 items-start gap-2">
                                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-sm" style="background-color: {{ $parentBidang['warna'] ?? '#64748b' }}"></span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold leading-snug text-slate-800">{{ $subBidang['kode'] }} · {{ $subBidang['nama'] }}</p>
                                            <p class="mt-0.5 text-[10px] text-slate-400">{{ $subBidang['nama_bidang'] }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-bold text-slate-800">{{ number_format($subBidang['jumlah_entri']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Akses Cepat Peta GIS -->
            <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm border border-slate-800 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 opacity-20 pointer-events-none">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=800&auto=format&fit=crop" alt="GIS Map" class="w-full h-full object-cover">
                </div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-white">Akses Cepat GIS</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">Live Map</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Eksplorasi spasial wilayah konservasi, titik koordinat patroli, dan batas kawasan hutan di Sulawesi Tengah secara terintegrasi.
                    </p>

                    <div class="mt-6 bg-slate-800/80 backdrop-blur rounded-xl p-4 border border-slate-700 h-36 flex items-center justify-center relative shadow-inner">
                        <div class="text-center">
                            <i class="fa-solid fa-map-location-dot text-3xl text-emerald-400 mb-2 animate-bounce"></i>
                            <p class="text-xs font-semibold text-slate-200">Peta Spasial Sulteng Aktif</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $totalLokasi ?? 0 }} Titik Koordinat Terpetakan</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 mt-6">
                    <a href="{{ route('konservasi.peta') }}" class="w-full py-2.5 px-4 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold rounded-xl text-xs text-center block transition shadow-md">
                        Jelajahi Peta GIS Interaktif <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Grafik jumlah entri per bidang -->
            <div class="lg:col-span-3 bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-5">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-700 mb-1">
                            <i class="fa-solid fa-chart-column text-sm"></i>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider">Perbandingan Bidang</span>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900">Jumlah Entri per Bidang</h3>
                        <p class="text-xs text-slate-500 mt-1">Total data tersimpan untuk masing-masing dari enam bidang.</p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500">{{ number_format($totalData ?? 0) }} entri tersimpan</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(260px,0.8fr)] gap-6 items-start">
                    <div class="relative h-[320px] min-w-0">
                        <canvas id="bidangChart" aria-label="Jumlah entri pada enam bidang konservasi" role="img"></canvas>
                    </div>
                    <div class="lg:border-l lg:border-slate-100 lg:pl-6">
                        <h4 class="mb-4 text-xs font-bold text-slate-800">Cakupan Sub-Bidang</h4>
                        <div class="space-y-4">
                            @foreach($bidangChart as $bidang)
                                @php
                                    $coverage = $bidang['total_subbidang'] > 0
                                        ? ($bidang['subbidang_terisi'] / $bidang['total_subbidang']) * 100
                                        : 0;
                                @endphp
                                <div>
                                    <div class="mb-1.5 flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-start gap-2">
                                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-sm" style="background-color: {{ $bidang['warna'] }}"></span>
                                            <span class="text-[11px] font-semibold leading-snug text-slate-700">{{ $bidang['nama'] }}</span>
                                        </div>
                                        <span class="shrink-0 text-[11px] font-bold text-slate-800">{{ number_format($bidang['jumlah_entri']) }}</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full" style="width: {{ $coverage }}%; background-color: {{ $bidang['warna'] }}"></div>
                                    </div>
                                    <p class="mt-1 text-[10px] text-slate-400">{{ $bidang['subbidang_terisi'] }} dari {{ $bidang['total_subbidang'] }} sub-bidang terisi</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bidangStats = @js($bidangChart ?? []);
            const subBidangStats = @js($subBidangChart ?? []);
            const numberFormat = new Intl.NumberFormat('id-ID');
            const sharedOptions = {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 650 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 11,
                        callbacks: {
                            label(context) {
                                return ` ${numberFormat.format(context.raw)} entri`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#64748b' },
                        grid: { color: '#e2e8f0' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#334155', font: { weight: '600' } }
                    }
                }
            };

            new Chart(document.getElementById('bidangChart'), {
                type: 'bar',
                data: {
                    labels: bidangStats.map((bidang) => bidang.kode),
                    datasets: [{
                        data: bidangStats.map((bidang) => bidang.jumlah_entri),
                        backgroundColor: bidangStats.map((bidang) => bidang.warna),
                        borderRadius: 5,
                        barThickness: 24,
                    }]
                },
                options: {
                    ...sharedOptions,
                    plugins: {
                        ...sharedOptions.plugins,
                        tooltip: {
                            ...sharedOptions.plugins.tooltip,
                            callbacks: {
                                title(context) {
                                    return bidangStats[context[0].dataIndex].nama;
                                },
                                label(context) {
                                    return ` ${numberFormat.format(context.raw)} entri`;
                                }
                            }
                        }
                    }
                },
            });

            const subBidangCanvas = document.getElementById('subBidangChart');
            if (subBidangCanvas && subBidangStats.length) {
                const subBidangColors = subBidangStats.map((item) => {
                    const bidang = bidangStats.find((field) => item.kode.startsWith(field.kode + '.'));
                    return bidang?.warna ?? '#64748b';
                });

                new Chart(subBidangCanvas, {
                    type: 'bar',
                    data: {
                        labels: subBidangStats.map((item) => item.kode),
                        datasets: [{
                            data: subBidangStats.map((item) => item.jumlah_entri),
                            backgroundColor: subBidangColors,
                            borderRadius: 5,
                            barThickness: 20,
                        }]
                    },
                    options: {
                        ...sharedOptions,
                        plugins: {
                            ...sharedOptions.plugins,
                            tooltip: {
                                ...sharedOptions.plugins.tooltip,
                                callbacks: {
                                    title(context) {
                                        const item = subBidangStats[context[0].dataIndex];
                                        return `${item.kode} · ${item.nama}`;
                                    },
                                    label(context) {
                                        const item = subBidangStats[context.dataIndex];
                                        return ` ${numberFormat.format(context.raw)} entri · ${item.nama_bidang}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            ...sharedOptions.scales,
                            x: {
                                ...sharedOptions.scales.x,
                                ticks: {
                                    ...sharedOptions.scales.x.ticks,
                                    callback(value) {
                                        return numberFormat.format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>