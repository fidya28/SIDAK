<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS - SINTUVU | Sistem Informasi dan Data Konservasi Terpadu Sulawesi Tengah</title>
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
    
    <!-- Leaflet GIS Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #map { height: 680px; width: 100%; border-radius: 1rem; }
        .gis-marker { background: transparent; border: 0; }
        .gis-marker i { font-size: 30px; line-height: 36px; text-shadow: 0 1px 3px rgba(15, 23, 42, .55); }
        .gis-marker-tooltip { background: white; border: 1px solid #cbd5e1; border-radius: 7px; box-shadow: 0 2px 8px rgba(15, 23, 42, .18); color: #334155; font-size: 10px; font-weight: 600; line-height: 1.35; padding: 4px 7px; text-align: center; }
        .gis-marker-tooltip::before { border-top-color: white; }
        .gis-popup { min-width: 220px; max-width: 340px; color: #334155; font: 12px/1.45 'Plus Jakarta Sans', sans-serif; }
        .gis-popup-title { border-bottom: 1px solid #e2e8f0; margin-bottom: 7px; padding-bottom: 7px; }
        .gis-popup-title strong { display: block; font-size: 13px; color: #0f172a; }
        .gis-popup-meta { color: #64748b; font-size: 10px; }
        .gis-popup dl { display: grid; grid-template-columns: minmax(82px, 38%) minmax(0, 1fr); gap: 5px 8px; margin: 0; }
        .gis-popup dt { color: #64748b; font-weight: 600; }
        .gis-popup dd { margin: 0; color: #0f172a; overflow-wrap: anywhere; white-space: pre-wrap; }
        .gis-popup dd a { color: #047857; font-weight: 700; }
    </style>
    @include('components.theme-assets')
</head>
<body class="bg-slate-100/90 font-sans text-slate-800 antialiased min-h-screen relative">

    <!-- BACKGROUND GLOBAL KAWASAN KONSERVASI DENGAN OPASITAS TIPIS -->
    <div class="fixed inset-0 pointer-events-none z-[-1] overflow-hidden">
        <img src="https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1920&auto=format&fit=crop" alt="Background Konservasi" class="w-full h-full object-cover opacity-15">
        <div class="absolute inset-0 bg-slate-100/75 backdrop-blur-[2px]"></div>
    </div>

    <!-- NAVBAR UTAMA (SERAGAM DENGAN DASHBOARD, REKAPITULASI, & INPUT DATA) -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo & Title Instansi -->
            <div class="flex items-center space-x-3">
                <div class="w-[42px] h-[42px] rounded-full bg-white border border-slate-200 flex items-center justify-center p-[3px] shadow-sm overflow-hidden">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Logo SINTUVU" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://via.placeholder.com/50?text=SINTUVU';">
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm tracking-tight text-slate-900 leading-tight">SINTUVU</span>
                    <span class="text-[11px] text-slate-500 font-medium">BKSDA Sulawesi Tengah</span>
                </div>
            </div>

            <!-- Menu Navigasi (Menu Peta GIS Aktif) -->
            <nav class="hidden md:flex items-center space-x-1 bg-slate-100/80 p-1.5 rounded-xl border border-slate-200">
                <a href="{{ route('konservasi.dashboard') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Dashboard</a>
                <a href="{{ route('konservasi.index') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Rekapitulasi</a>
                <a href="{{ route('konservasi.create') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Tambah Data</a>
                <a href="{{ route('konservasi.peta') }}" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white text-forest-700 shadow-xs">GIS</a>
            </nav>

            <!-- Status & User Profile (Dinamis) -->
            <div class="flex items-center space-x-4">
                <span data-system-status class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
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

        <!-- HEADER BANNER & RINGKASAN SEBARAN -->
        <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-forest-700 uppercase tracking-wider bg-emerald-100/70 px-2.5 py-0.5 rounded-md">GIS Spasial</span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-500">Sulawesi Tengah</span>
                </div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight">
                    GIS
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Visualisasi batas interaktif kawasan hutan konservasi dan titik pemantauan Balai KSDA Sulawesi Tengah
                </p>
            </div>

            <!-- Legenda Ringkas -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-700">
                    <span class="w-3.5 h-3.5 rounded-sm bg-emerald-500/60 border border-emerald-600"></span>
                    <span>Poligon Kawasan</span>
                </div>
                <span class="h-4 w-px bg-slate-300"></span>
                <span title="Perencanaan Konservasi" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#0f766e"></i>A</span>
                <span title="Konservasi Kawasan" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#ca8a04"></i>B</span>
                <span title="Konservasi Spesies dan Genetik" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#e11d48"></i>C</span>
                <span title="Pemanfaatan Jasa Lingkungan" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#0284c7"></i>D</span>
                <span title="Pemulihan Ekosistem dan Bina Area Preservasi" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#65a30d"></i>E</span>
                <span title="Kesekretariatan" class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-700"><i class="fa-solid fa-location-dot" style="color:#ea580c"></i>F</span>
                <span class="text-[10px] text-slate-500">Titik data</span>
            </div>
        </div>

        <!-- CONTAINER PETA GIS LEAFLET -->
        <div class="bg-white/90 backdrop-blur-md p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm relative">
            <div id="map" class="shadow-inner rounded-xl z-10"></div>
        </div>

    </main>

    <!-- Leaflet Map Logic -->
    <script>
        // 1. Inisialisasi Peta Fokus Langsung ke Sulawesi Tengah (Zoom Level 7)
        var map = L.map('map', {
            minZoom: 6,
            maxZoom: 18
        }).setView([-1.2, 120.8], 7);

        // 2. Basemap Satelite Esri & OpenStreetMap
        var sateliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; BKSDA Sulawesi Tengah'
        });

        var streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; BKSDA Sulawesi Tengah | OpenStreetMap'
        });

        // Set Default ke Peta Satelit
        sateliteMap.addTo(map);

        // Switcher Layer
        var baseMaps = {
            "Peta Satelit (Esri)": sateliteMap,
            "Peta Jalan (OSM)": streetMap
        };
        L.control.layers(baseMaps).addTo(map);

        // 3. MUAT GEOJSON KAWASAN KONSERVASI BKSDA SULTENG
        fetch("{{ asset('Kawasan_Hutan_Konservasi_SINTUVU_2.json') }}")
            .then(response => response.json())
            .then(data => {
                var geojsonLayer = L.geoJSON(data, {
                    style: function(feature) {
                        return {
                            color: "#22c55e",       // Batas Luar (Hijau Terang)
                            weight: 2,
                            opacity: 1,
                            fillColor: "#16a34a",   // Isian Area Kawasan
                            fillOpacity: 0.5
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        if (feature.properties) {
                            var namaKawasan = feature.properties.NAMOBJ || "Kawasan Konservasi";
                            var fungsi = feature.properties.REMARK || "BKSDA Sulteng";
                            var kabupaten = feature.properties.WADMKK || "-";

                            layer.bindTooltip(namaKawasan, {
                                permanent: false,
                                direction: "center",
                                className: "bg-forest-800 text-white font-bold px-2.5 py-1 rounded-lg text-xs border border-forest-600 shadow-md"
                            });

                            layer.bindPopup(`
                                <div class="p-1 min-w-[180px]">
                                    <strong class="text-[10px] text-forest-700 block uppercase tracking-wider font-extrabold">BKSDA SULAWESI TENGAH</strong>
                                    <h4 class="font-bold text-sm text-slate-800 leading-snug mt-0.5">${namaKawasan}</h4>
                                    <hr class="my-2 border-slate-200">
                                    <p class="text-xs text-slate-600">Kabupaten/Kota: <strong>${kabupaten}</strong></p>
                                    <p class="text-xs text-slate-500 mt-1">Fungsi: <span>${fungsi}</span></p>
                                </div>
                            `);
                        }
                    }
                }).addTo(map);

                // Auto zoom memuat seluruh wilayah kawasan
                map.fitBounds(geojsonLayer.getBounds());
            })
            .catch(error => console.error("Error GeoJSON:", error));

        // 4. Marker Lokasi Database
        const locations = {{ \Illuminate\Support\Js::from($locations ?? []) }};
        const bidangColors = {
            A: '#0f766e',
            B: '#ca8a04',
            C: '#e11d48',
            D: '#0284c7',
            E: '#65a30d',
            F: '#ea580c',
        };

        function escapeHtml(value) {
            const element = document.createElement('span');
            element.textContent = String(value ?? '');
            return element.innerHTML;
        }

        if (Array.isArray(locations)) {
            locations.forEach(function(item) {
                if (item.latitude === null || item.latitude === '' || item.longitude === null || item.longitude === '') {
                    return;
                }

                const latitude = Number(item.latitude);
                const longitude = Number(item.longitude);
                if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;

                const subBidang = item.sub_bidang || {};
                const kodeSubBidang = subBidang.kode_sub || '';
                const bidang = subBidang.bidang || {};
                const namaBidang = bidang.nama_bidang || 'Bidang tidak diketahui';
                const namaSubBidang = subBidang.nama_sub_bidang || 'Sub-bidang tidak diketahui';
                const color = bidangColors[kodeSubBidang.charAt(0).toUpperCase()] || '#64748b';
                const icon = L.divIcon({
                    className: 'gis-marker',
                    html: `<i class="fa-solid fa-location-dot" style="color:${color}"></i>`,
                    iconSize: [28, 36],
                    iconAnchor: [14, 34],
                    popupAnchor: [0, -32],
                });
                const marker = L.marker([latitude, longitude], { icon }).addTo(map);

                marker.bindTooltip(
                    `${escapeHtml(namaBidang)}<br><strong>${escapeHtml(kodeSubBidang)} - ${escapeHtml(namaSubBidang)}</strong>`,
                    { permanent: true, direction: 'top', offset: [0, -31], className: 'gis-marker-tooltip' }
                );

                const detailRows = String(item.keterangan || '').split(' | ').map(function(detail) {
                    const separator = detail.indexOf(': ');
                    if (separator < 0) {
                        return `<dt>Keterangan</dt><dd>${escapeHtml(detail)}</dd>`;
                    }

                    return `<dt>${escapeHtml(detail.slice(0, separator))}</dt><dd>${escapeHtml(detail.slice(separator + 2))}</dd>`;
                }).join('');

                marker.bindPopup(`
                    <div class="gis-popup">
                        <div class="gis-popup-title">
                            <span class="gis-popup-meta" style="color:${color}">${escapeHtml(namaBidang)}</span>
                            <strong>${escapeHtml(kodeSubBidang)} - ${escapeHtml(namaSubBidang)}</strong>
                            <span class="gis-popup-meta">Tahun ${escapeHtml(item.tahun ?? '-')} | Jumlah ${escapeHtml(item.jumlah ?? '-')}</span>
                        </div>
                        <dl>${detailRows || '<dt>Rincian</dt><dd>-</dd>'}</dl>
                    </div>
                `);
            });
        }
    </script>
</body>
</html>