<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SINTUVU | Sistem Informasi dan Data Konservasi Terpadu Sulawesi Tengah - Edit Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100/90 font-sans text-slate-800 antialiased min-h-screen relative">
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="{{ route('konservasi.dashboard') }}" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-700/10 border border-emerald-700/20 flex items-center justify-center p-1.5 shadow-xs overflow-hidden">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Logo SINTUVU" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm text-slate-900 leading-tight">SINTUVU</span>
                    <span class="text-[11px] text-slate-500 font-medium">BKSDA Sulawesi Tengah</span>
                </div>
            </a>
            <nav class="hidden md:flex items-center space-x-1 bg-slate-100/80 p-1.5 rounded-xl border border-slate-200">
                <a href="{{ route('konservasi.dashboard') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Dashboard</a>
                <a href="{{ route('konservasi.index') }}" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white text-emerald-700 shadow-xs">Rekapitulasi</a>
                <a href="{{ route('konservasi.create') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">Tambah Data</a>
                <a href="{{ route('konservasi.peta') }}" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-white/50 transition">GIS</a>
            </nav>
            <div class="flex items-center gap-2.5 border-l pl-4 border-slate-200">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shadow-sm uppercase">
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
    </header>

    <div class="min-h-screen">

        <!-- MAIN CONTENT -->
        <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div>
                <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Form Header Banner -->
                    <div class="p-6 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-700 uppercase">Mode Perubahan Data</span>
                            <h2 class="text-2xl font-extrabold mt-1 text-slate-900">Edit Data Konservasi</h2>
                            <p class="text-xs text-slate-500 mt-1">Perbarui informasi data capaian kinerja dan inventarisasi kawasan.</p>
                        </div>
                        <a href="{{ route('konservasi.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-200/80 hover:bg-slate-300 transition shadow-xs">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Rekapitulasi
                        </a>
                    </div>

                    <!-- Form Input -->
                    <form action="{{ route('konservasi.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8" x-data="{ selectedSubKode: @js($currentKode), kerjaSamaBaruF07: @js(old('perjanjian_baru_f07', $detailValues['perjanjian_baru_f07'] ?? '')), statusKerjaSamaF07: @js(old('status_kerja_sama_f07', $detailValues['status_kerja_sama_f07'] ?? '')) }">
                        @csrf
                        @method('PUT')

                        <!-- Section 1: Klasifikasi -->
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Klasifikasi & Kategori Data
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Bidang Utama -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Bidang Utama *</label>
                                    <div class="relative">
                                        <select id="bidang_id" name="bidang_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-600 focus:bg-white transition appearance-none font-medium text-slate-800 pr-10" required>
                                            <option value="">-- Pilih Bidang Utama --</option>
                                            @foreach($bidangs as $b)
                                                <option value="{{ $b->id }}" {{ (isset($currentBidangId) && $currentBidangId == $b->id) ? 'selected' : '' }}>
                                                    {{ $b->nama_bidang }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                                    </div>
                                </div>

                                <!-- Sub Bidang -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sub-Bidang Kategori *</label>
                                    <div class="relative">
                                        <select id="sub_bidang_id" name="sub_bidang_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-600 focus:bg-white transition appearance-none font-medium text-slate-800 pr-10" required>
                                            <option value="">-- Pilih Sub-Bidang --</option>
                                            @foreach($subBidangs as $sb)
                                                <option value="{{ $sb->id }}" data-kode="{{ $sb->kode_sub }}" {{ $item->sub_bidang_id == $sb->id ? 'selected' : '' }}>
                                                    {{ $sb->nama_sub_bidang }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        @php
                            $editRadioOptions = [
                                'ada_perubahan' => ['ya' => 'Ya, ada perubahan', 'tidak' => 'Tidak ada'],
                                'sk_provinsi_tersedia' => ['ya' => 'Ya, tersedia', 'tidak' => 'Tidak'],
                                'sk_penetapan_tersedia' => ['ya' => 'Ya, tersedia', 'tidak' => 'Tidak'],
                                'ketersediaan_rpjp' => ['ya' => 'Ya, tersedia', 'tidak' => 'Tidak'],
                                'ada_kegiatan_monitoring' => ['ya' => 'Ada kegiatan', 'tidak' => 'Tidak ada'],
                                'jenis_kegiatan' => ['Penataan' => 'Penataan', 'Rekonstruksi' => 'Rekonstruksi', 'Pemeliharaan' => 'Pemeliharaan'],
                                'ketersediaan_evaluasi' => ['ya' => 'Ya, tersedia', 'tidak' => 'Tidak'],
                                'ketersediaan_ekosistem' => ['ya' => 'Ya, tersedia', 'tidak' => 'Tidak'],
                                'ketersediaan_zonasi' => ['sudah' => 'Sudah', 'belum' => 'Belum'],
                                'ada_kegiatan_b01' => ['ya' => 'Ada kegiatan', 'tidak' => 'Tidak ada'],
                                'ada_akses_b02' => ['ya' => 'Ya, ada', 'tidak' => 'Tidak ada (Nihil)'],
                                'perjanjian_baru_f07' => ['ya' => 'Ya, ada perjanjian baru', 'tidak' => 'Nihil'],
                                'jenis_pengelolaan_b02' => ['akses_tradisional' => 'Pemberian Akses Pemanfaatan Tradisional', 'kemitraan_konservasi' => 'Kemitraan Konservasi'],
                                'jenis_akses_b02' => ['pemungutan_hhbk' => 'Pemungutan HHBK', 'budidaya_tradisional' => 'Budidaya Tradisional', 'perburuan_tradisional' => 'Perburuan Tradisional', 'pemanfaatan_sda_perairan' => 'Pemanfaatan SDA Perairan', 'lainnya' => 'Lainnya'],
                                'masyarakat_hukum_adat_b02' => ['ya' => 'Ya', 'tidak' => 'Tidak/Bukan'],
                                'sumber_dana' => ['APBN KLHK Lainnya' => 'APBN KLHK Lainnya', 'APBN KSDAE' => 'APBN KSDAE', 'Pendanaan Pihak Lainnya' => 'Pendanaan Pihak Lainnya', 'Pendanaan Gabungan' => 'Pendanaan Gabungan'],
                            ];
                            $editSelectOptions = [
                                'jenis_jabatan_fungsional_f04' => $jenisJabatanFungsionalF04,
                                'jenis_jabatan_fungsional_f05' => $jenisJabatanFungsionalF04,
                                'periode_semester_f07' => ['Semester I', 'Semester II'],
                                'jenis_dokumen_f07' => ['PKS', 'MOU', 'Potensi'],
                                'tipe_mitra_f07' => $tipeMitraF07,
                            ];
                            $editDateFields = ['sk_parsial_tanggal', 'sk_provinsi_tanggal', 'sk_penetapan_tanggal', 'sk_rpjp_tanggal_pengesahan', 'sk_rpjp_periode_berakhir', 'tanggal_batb', 'tanggal_pelaksanaan_evaluasi', 'tanggal_sk_zonasi', 'tanggal_mulai_ks_b02', 'tanggal_akhir_pks_b02', 'tanggal_mulai_f07', 'tanggal_berakhir_f07'];
                            $editNumberFields = ['sk_parsial_luas', 'sk_provinsi_luas', 'sk_penetapan_luas', 'pal_baik', 'pal_rusak', 'pal_hilang', 'pal_total', 'panjang_pal_km', 'jumlah_laki', 'jumlah_perempuan', 'jumlah_bantuan', 'jumlah_laki_b02', 'jumlah_perempuan_b02', 'luas_area_b02', 'luas_area_f07', 'nilai_komitmen_f07', 'struktural_i_a_laki_f01', 'struktural_i_a_perempuan_f01', 'struktural_i_b_laki_f01', 'struktural_i_b_perempuan_f01', 'struktural_ii_a_laki_f01', 'struktural_ii_a_perempuan_f01', 'struktural_ii_b_laki_f01', 'struktural_ii_b_perempuan_f01', 'struktural_iii_a_laki_f01', 'struktural_iii_a_perempuan_f01', 'struktural_iii_b_laki_f01', 'struktural_iii_b_perempuan_f01', 'struktural_iv_a_laki_f01', 'struktural_iv_a_perempuan_f01', 'struktural_iv_b_laki_f01', 'struktural_iv_b_perempuan_f01', 'fungsional_khusus_laki_f01', 'fungsional_khusus_perempuan_f01', 'calon_fungsional_khusus_laki_f01', 'calon_fungsional_khusus_perempuan_f01', 'fungsional_umum_laki_f01', 'fungsional_umum_perempuan_f01', 'doktor_s3_laki_f02', 'doktor_s3_perempuan_f02', 'master_s2_laki_f02', 'master_s2_perempuan_f02', 'sarjana_s1_laki_f02', 'sarjana_s1_perempuan_f02', 'diploma_d3_laki_f02', 'diploma_d3_perempuan_f02', 'slta_sma_laki_f02', 'slta_sma_perempuan_f02', 'sltp_smp_laki_f02', 'sltp_smp_perempuan_f02', 'sekolah_dasar_sd_laki_f02', 'sekolah_dasar_sd_perempuan_f02', 'golongan_iv_laki_f03', 'golongan_iv_perempuan_f03', 'golongan_iii_laki_f03', 'golongan_iii_perempuan_f03', 'golongan_ii_laki_f03', 'golongan_ii_perempuan_f03', 'golongan_i_laki_f03', 'golongan_i_perempuan_f03', 'laki_f04', 'perempuan_f04', 'doktor_s3_laki_f05', 'doktor_s3_perempuan_f05', 'master_s2_laki_f05', 'master_s2_perempuan_f05', 'sarjana_s1_laki_f05', 'sarjana_s1_perempuan_f05', 'diploma_d3_laki_f05', 'diploma_d3_perempuan_f05', 'slta_sma_laki_f05', 'slta_sma_perempuan_f05', 'sltp_smp_laki_f05', 'sltp_smp_perempuan_f05', 'sekolah_dasar_sd_laki_f05', 'sekolah_dasar_sd_perempuan_f05', 'master_s2_laki_f06', 'master_s2_perempuan_f06', 'sarjana_s1_laki_f06', 'sarjana_s1_perempuan_f06', 'diploma_d3_laki_f06', 'diploma_d3_perempuan_f06', 'slta_sma_laki_f06', 'slta_sma_perempuan_f06', 'sltp_smp_laki_f06', 'sltp_smp_perempuan_f06', 'sekolah_dasar_sd_laki_f06', 'sekolah_dasar_sd_perempuan_f06'];
                            $editTextareaFields = ['rekomendasi_evaluasi', 'tindak_lanjut_evaluasi', 'jenis_dimanfaatkan_b02', 'ruang_lingkup_f07', 'catatan_dukungan_f07', 'lokasi_detail_f07', 'keterangan'];
                            $editFileFields = ['sk_parsial_file', 'sk_provinsi_file', 'sk_penetapan_file', 'shapefile_zip', 'sk_rpjp_file', 'dokumen_batb', 'shapefile_monitoring_zip', 'file_dokumen_evaluasi', 'shapefile_ekosistem_zip', 'file_sk_zonasi', 'shapefile_zonasi_zip', 'dokumen_d03', 'shapefile_d03', 'foto_odtwa', 'dokumen_perizinan_pbp_d06', 'shapefile_pbp_d06', 'foto_sarana_prasarana_d07', 'shapefile_d10', 'dokumen_sk_d11', 'shapefile_d11', 'dokumen_perizinan_d14', 'shapefile_d14', 'dokumen_perizinan_c02', 'dokumen_perizinan_c04', 'dokumen_perizinan_c06', 'dokumen_kerjasama_b02', 'shapefile_kerjasama_b02', 'dokumen_kerja_sama_f07', 'shapefile_f07'];
                            $editImageFields = ['foto_odtwa', 'foto_sarana_prasarana_d07'];
                            $editCheckboxFields = ['hhbk_nihil', 'pertanian_nihil', 'perkebunan_nihil', 'peternakan_nihil', 'perikanan_nihil', 'wisata_nihil', 'produk_nihil', 'pembibitan_nihil', 'lainnya_nihil'];
                        @endphp

                        <div class="space-y-6" x-cloak x-show="selectedSubKode && {{ Js::from(array_keys($editFieldsBySubBidang)) }}.includes(selectedSubKode)">
                            @foreach($editFieldsBySubBidang as $kode => $fields)
                                <section x-cloak x-show="selectedSubKode === @js($kode)" class="space-y-5">
                                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200/80">
                                        <span class="w-7 h-7 rounded-lg bg-emerald-700 text-amber-400 flex items-center justify-center text-xs font-black">2</span>
                                        <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">{{ $kode === 'F.07' ? 'INFORMASI KERJA SAMA' : 'Data Sub-Bidang ' . $kode }}</h3>
                                    </div>
                                    @if($kode === 'F.08')
                                        @php
                                            $f08Year = old('tahun_f08', $detailValues['tahun_f08'] ?? $item->tahun);
                                            $f08Month = old('periode_bulan_f08', $detailValues['periode_bulan_f08'] ?? $item->bulan);
                                            $f08Unit = old('satuan_kerja_f08', $detailValues['satuan_kerja_f08'] ?? '');
                                            $f08Months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                            $f08Categories = [
                                                'penelitian' => 'PENELITIAN DAN PENGEMBANGAN',
                                                'pendidikan' => 'PENDIDIKAN DAN ILMU PENGETAHUAN',
                                                'foto_video' => 'PENGAMBILAN FOTO DAN VIDEO',
                                                'lain_lain' => 'LAIN-LAIN',
                                            ];
                                        @endphp
                                        <div class="space-y-8">
                                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                                <div>
                                                    <label for="tahun_f08" class="mb-2 block text-sm font-semibold text-slate-700">Tahun: <span class="text-rose-500">*</span></label>
                                                    <select id="tahun_f08" name="tahun_f08" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                                        <option value="">Pilih tahun</option>
                                                        @for($year = 2026; $year >= 1945; $year--)
                                                            <option value="{{ $year }}" {{ (string) $f08Year === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="periode_bulan_f08" class="mb-2 block text-sm font-semibold text-slate-700">Periode Bulan: <span class="text-rose-500">*</span></label>
                                                    <select id="periode_bulan_f08" name="periode_bulan_f08" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                                        <option value="">Pilih periode</option>
                                                        @foreach($f08Months as $index => $month)
                                                            <option value="{{ $index + 1 }}" {{ (string) $f08Month === (string) ($index + 1) ? 'selected' : '' }}>{{ $month }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="satuan_kerja_f08" class="mb-2 block text-sm font-semibold text-slate-700">Satuan Kerja: <span class="text-rose-500">*</span></label>
                                                    <select id="satuan_kerja_f08" name="satuan_kerja_f08" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                                        <option value="">Pilih satuan kerja</option>
                                                        <option value="Balai KSDA Sulawesi Tengah" {{ $f08Unit === 'Balai KSDA Sulawesi Tengah' ? 'selected' : '' }}>Balai KSDA Sulawesi Tengah</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @foreach($f08Categories as $category => $categoryLabel)
                                                <fieldset class="rounded-xl border border-slate-200 bg-white px-5 pb-6 pt-2 shadow-lg shadow-slate-900/10">
                                                    <legend class="px-2 text-sm font-extrabold text-blue-600">{{ $categoryLabel }}</legend>
                                                    <div class="grid grid-cols-1 gap-5 pt-3 md:grid-cols-2">
                                                        @foreach(['dalam_negeri' => 'Dalam Negeri', 'luar_negeri' => 'Luar Negeri'] as $region => $regionLabel)
                                                            @php
                                                                $countField = 'jumlah_' . $category . '_' . $region . '_f08';
                                                                $countValue = old($countField, $detailValues[$countField] ?? '');
                                                            @endphp
                                                            <div>
                                                                <label for="{{ $countField }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ $regionLabel }}: <span class="text-rose-500">*</span></label>
                                                                <div class="flex overflow-hidden rounded-md border border-slate-300 bg-white">
                                                                    <span class="inline-flex items-center border-r border-slate-300 bg-slate-50 px-3 text-sm text-slate-600">Jumlah</span>
                                                                    <input id="{{ $countField }}" type="number" name="{{ $countField }}" value="{{ $countValue }}" min="0" step="1" required class="w-full min-w-0 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                                                    <span class="inline-flex items-center border-l border-slate-300 bg-slate-50 px-3 text-sm text-slate-600">Orang</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </fieldset>
                                            @endforeach

                                            <div>
                                                <label for="keterangan_f08" class="mb-2 block text-sm font-semibold text-slate-700">Keterangan:</label>
                                                <textarea id="keterangan_f08" name="keterangan" rows="4" placeholder="Masukan disini untuk informasi lainnya" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">{{ old('keterangan', $detailValues['keterangan'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    @else
                                    <div class="grid grid-cols-1 gap-5 {{ $kode === 'F.07' ? 'md:grid-cols-12' : 'md:grid-cols-2' }}">
                                        @foreach($fields as $field)
                                            @php
                                                $name = $field['name'];
                                                $value = old($name, $detailValues[$name] ?? (str_starts_with($name, 'tahun') ? $item->tahun : ''));
                                                $requiredF01 = $kode === 'F.01' && ($name === 'tahun_f01' || $name === 'satuan_kerja_f01' || in_array($name, $editNumberFields, true));
                                                $requiredF02 = $kode === 'F.02' && ($name === 'tahun_f02' || $name === 'satuan_kerja_f02' || in_array($name, $editNumberFields, true));
                                                $requiredF03 = $kode === 'F.03' && ($name === 'tahun_f03' || $name === 'satuan_kerja_f03' || in_array($name, $editNumberFields, true));
                                                $requiredF04 = $kode === 'F.04' && in_array($name, ['tahun_f04', 'satuan_kerja_f04', 'jenis_jabatan_fungsional_f04', 'laki_f04', 'perempuan_f04'], true);
                                                $requiredF05 = $kode === 'F.05' && ($name === 'tahun_f05' || $name === 'satuan_kerja_f05' || $name === 'jenis_jabatan_fungsional_f05' || in_array($name, $editNumberFields, true));
                                                $requiredF06 = $kode === 'F.06' && ($name === 'tahun_f06' || $name === 'satuan_kerja_f06' || in_array($name, $editNumberFields, true));
                                                $requiredF07 = $kode === 'F.07' && in_array($name, ['tahun_f07', 'periode_semester_f07', 'satuan_kerja_f07', 'perjanjian_baru_f07', 'jenis_dokumen_f07', 'nama_mitra_f07', 'tipe_mitra_f07', 'status_kerja_sama_f07', 'nomor_perjanjian_f07', 'tanggal_mulai_f07', 'tanggal_berakhir_f07', 'judul_kerja_sama_f07', 'ruang_lingkup_f07', 'provinsi_f07'], true);
                                                $conditionalF07 = $kode === 'F.07' && !in_array($name, ['tahun_f07', 'periode_semester_f07', 'satuan_kerja_f07', 'perjanjian_baru_f07'], true);
                                                $selectedMultiValues = in_array($name, ['provinsi_f07', 'dukungan_klhk_f07'], true)
                                                    ? (json_decode((string) $value, true) ?: (filled($value) ? explode(', ', (string) $value) : []))
                                                    : [];
                                                $f07FullWidth = in_array($name, ['status_kerja_sama_f07', 'judul_kerja_sama_f07', 'ruang_lingkup_f07', 'dukungan_klhk_f07', 'catatan_dukungan_f07', 'dokumen_kerja_sama_f07', 'provinsi_f07', 'lokasi_detail_f07', 'shapefile_f07', 'keterangan'], true);
                                                $f07ColumnWidth = match ($name) {
                                                    'tanggal_mulai_f07', 'tanggal_berakhir_f07' => 'md:col-span-3',
                                                    'nomor_perjanjian_f07' => 'md:col-span-6',
                                                    'status_kerja_sama_f07' => 'md:col-span-12',
                                                    default => $f07FullWidth ? 'md:col-span-12' : 'md:col-span-6',
                                                };
                                                $wrapperWidth = $kode === 'F.07'
                                                    ? $f07ColumnWidth
                                                    : (in_array($name, $editTextareaFields, true) || in_array($name, ['provinsi_f07', 'dukungan_klhk_f07'], true) ? 'md:col-span-2' : '');
                                            @endphp
                                            <div class="{{ $wrapperWidth }}" @if($conditionalF07) x-show="kerjaSamaBaruF07 === 'ya'" @endif>
                                                @if($name !== 'keterangan')
                                                    <label class="block {{ $kode === 'F.07' ? 'text-sm font-semibold' : 'text-xs font-bold' }} text-slate-700 mb-2">{{ $field['label'] }} @if($requiredF01 || $requiredF02 || $requiredF03 || $requiredF04 || $requiredF05 || $requiredF06 || $requiredF07)<span class="text-rose-500">*</span>@endif</label>
                                                @endif
                                                @if(isset($editRadioOptions[$name]))
                                                    <div class="flex flex-wrap gap-x-5 gap-y-3 py-2">
                                                        @foreach($editRadioOptions[$name] as $optionValue => $optionLabel)
                                                            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                                                <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'checked' : '' }} @if($name === 'perjanjian_baru_f07') x-model="kerjaSamaBaruF07" required @endif class="text-emerald-700 focus:ring-emerald-600">
                                                                {{ $optionLabel }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @elseif($name === 'status_kerja_sama_f07')
                                                    <div class="flex flex-col items-start gap-2.5" role="group" aria-label="Status Kerja Sama">
                                                        @foreach(['Aktif', 'Berakhir', 'Masa Perpanjangan', 'Potensi', 'Proses Perpanjangan'] as $status)
                                                            <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                                                                <input type="radio" name="{{ $name }}" value="{{ $status }}" {{ (string) $value === $status ? 'checked' : '' }} x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" x-bind:required="kerjaSamaBaruF07 === 'ya'" class="h-5 w-5 border-slate-300 text-blue-600 focus:ring-blue-500">
                                                                {{ $status }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @elseif($name === 'provinsi_f07' || $name === 'dukungan_klhk_f07')
                                                    @php
                                                        $searchId = $name === 'provinsi_f07' ? 'provinsi_search_f07' : 'dukungan_klhk_search_f07';
                                                        $searchPlaceholder = $name === 'provinsi_f07' ? 'Cari provinsi...' : 'Cari IKSS / IKP / IKK...';
                                                    @endphp
                                                    <div x-data="{ open: false, query: '', selected: @js($selectedMultiValues) }" @click.outside="open = false" class="relative">
                                                        <div @click="$refs.optionSearch.focus()" class="flex min-h-14 flex-wrap items-center gap-2 rounded-xl border border-slate-300 bg-white p-2 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600">
                                                            <template x-for="option in selected" :key="option">
                                                                <span class="inline-flex max-w-full items-center gap-2 rounded-full bg-blue-600 px-3 py-1.5 text-sm font-medium text-white">
                                                                    <span class="break-all" x-text="option"></span>
                                                                    <button type="button" @click.stop="selected = selected.filter((item) => item !== option)" :aria-label="'Hapus ' + option" @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif class="shrink-0 text-lg leading-none hover:text-blue-100">&times;</button>
                                                                </span>
                                                            </template>
                                                            <input id="{{ $searchId }}" x-ref="optionSearch" type="search" x-model="query" @focus="open = true" @click="open = true" placeholder="{{ $searchPlaceholder }}" @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif class="min-w-[10rem] flex-1 border-0 bg-transparent px-2 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                                                        </div>
                                                        @if($name === 'dukungan_klhk_f07')
                                                            <p class="mt-1 text-sm text-slate-500">Pilih indikator dari referensi (terstruktur, boleh lebih dari satu).</p>
                                                        @endif
                                                        <div x-cloak x-show="open" x-transition class="absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                                                            <div role="listbox" aria-multiselectable="true" class="grid max-h-64 grid-cols-1 gap-1 overflow-y-auto {{ $name === 'provinsi_f07' ? 'sm:grid-cols-2' : '' }}">
                                                                @foreach($name === 'provinsi_f07' ? $provinsiF07 : $indikatorKlhkF07 as $option)
                                                                    <label x-show="@js(mb_strtolower($option)).includes(query.toLowerCase())" class="flex cursor-pointer items-start gap-2 rounded-lg px-2 py-2 text-xs leading-relaxed text-slate-700 hover:bg-slate-50">
                                                                        <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" x-model="selected" @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif class="mt-0.5 rounded text-emerald-700 focus:ring-emerald-600">
                                                                        <span>{{ $option }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif(in_array($name, $editCheckboxFields, true))
                                                    <label class="inline-flex items-center gap-2 py-2 text-xs text-slate-700">
                                                        <input type="checkbox" name="{{ $name }}" value="on" {{ in_array(strtolower((string) $value), ['ya', 'on', '1', 'true'], true) ? 'checked' : '' }} class="rounded text-emerald-700 focus:ring-emerald-600">
                                                        Nihil
                                                    </label>
                                                @elseif(in_array($name, $editFileFields, true))
                                                    @if($name === 'shapefile_f07')
                                                        <p class="mb-2 text-sm font-medium text-slate-700">Data area/lokasi kerjasama tersedia dalam bentuk (point/polygon)?</p>
                                                        <p class="mb-3 text-sm text-slate-600">ESRI Shapefile terdiri dari file dengan ekstensi .shp, .dbf, .prj, .shx, dan .cpg yang dikompres menjadi ekstensi .Zip</p>
                                                    @elseif($name === 'dokumen_kerja_sama_f07')
                                                        <p class="mb-2 text-sm text-slate-500">Format PDF · maks 20 MB.</p>
                                                    @endif
                                                    <input type="file" name="{{ $name }}{{ $name === 'foto_sarana_prasarana_d07' ? '[]' : '' }}" accept="{{ in_array($name, $editImageFields, true) ? 'image/png,image/jpeg' : (str_contains($name, 'zip') || str_starts_with($name, 'shapefile') ? '.zip' : '.pdf') }}" {{ $name === 'foto_sarana_prasarana_d07' ? 'multiple' : '' }} @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif class="w-full text-xs text-slate-500 border border-slate-200 bg-white rounded-xl p-2.5">
                                                    @if(!empty($value))
                                                        <p class="mt-1 text-[11px] text-slate-500">Lampiran tersimpan: {{ basename($value) }}. Kosongkan jika tidak ingin mengganti.</p>
                                                    @endif
                                                @elseif(in_array($name, $editTextareaFields, true))
                                                    @if($name === 'keterangan')
                                                        <label class="mb-2 block text-base font-semibold text-slate-700">Keterangan:</label>
                                                    @endif
                                                    <textarea name="{{ $name }}" rows="{{ $name === 'ruang_lingkup_f07' || $name === 'lokasi_detail_f07' || $name === 'keterangan' ? 4 : 3 }}" placeholder="{{ $name === 'ruang_lingkup_f07' ? 'Ruang lingkup kerja sama' : ($name === 'catatan_dukungan_f07' ? 'Catatan IK/IKP/IKK bila belum tersedia di referensi..' : ($name === 'lokasi_detail_f07' ? 'Masukan detail lokasi kerjasama disini..' : ($name === 'keterangan' ? 'Masukan disini untuk informasi lainnya' : ''))) }}" @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif @if(in_array($name, ['ruang_lingkup_f07'], true)) x-bind:required="kerjaSamaBaruF07 === 'ya'" @endif class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">{{ $value }}</textarea>
                                                @elseif(in_array($name, ['luas_area_f07', 'nilai_komitmen_f07'], true))
                                                    <div class="flex overflow-hidden rounded-xl border border-slate-300 bg-white">
                                                        @if($name === 'nilai_komitmen_f07')
                                                            <span class="inline-flex items-center border-r border-slate-300 bg-slate-50 px-4 text-sm text-slate-600">Rp</span>
                                                        @endif
                                                        <input type="number" min="0" step="0.01" name="{{ $name }}" value="{{ $value }}" @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif class="w-full min-w-0 border-0 px-4 py-3 text-sm focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                                        <span class="inline-flex items-center border-l border-slate-300 bg-slate-50 px-4 text-sm text-slate-600">{{ $name === 'luas_area_f07' ? 'Ha' : ',00' }}</span>
                                                    </div>
                                                @elseif(isset($editSelectOptions[$name]))
                                                    <select name="{{ $name }}" {{ $requiredF04 || $requiredF05 || ($requiredF07 && !$conditionalF07) ? 'required' : '' }} @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" x-bind:required="kerjaSamaBaruF07 === 'ya'" @endif class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-600 focus:bg-white transition font-medium text-slate-800">
                                                        <option value="">Pilih {{ $name === 'periode_semester_f07' ? 'periode' : ($name === 'tipe_mitra_f07' ? 'tipe mitra' : ($name === 'status_kerja_sama_f07' ? 'status' : ($name === 'jenis_dokumen_f07' ? 'jenis dokumen' : 'jenis jabatan'))) }}</option>
                                                        @foreach($editSelectOptions[$name] as $option)
                                                            @php
                                                                $optionValue = is_array($option) ? $option['value'] : $option;
                                                                $optionLabel = is_array($option) ? $option['label'] : $option;
                                                            @endphp
                                                            <option value="{{ $optionValue }}" {{ $value === $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif(in_array($name, ['satuan_kerja_f01', 'satuan_kerja_f02', 'satuan_kerja_f03', 'satuan_kerja_f04', 'satuan_kerja_f05', 'satuan_kerja_f06', 'satuan_kerja_f07'], true))
                                                    <select name="{{ $name }}" {{ $requiredF01 || $requiredF02 || $requiredF03 || $requiredF04 || $requiredF05 || $requiredF06 || $requiredF07 ? 'required' : '' }} class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-600 focus:bg-white transition font-medium text-slate-800">
                                                        <option value="">Pilih satuan kerja</option>
                                                        <option value="Balai KSDA Sulawesi Tengah" {{ $value === 'Balai KSDA Sulawesi Tengah' ? 'selected' : '' }}>Balai KSDA Sulawesi Tengah</option>
                                                    </select>
                                                @else
                                                    <input type="{{ in_array($name, $editDateFields, true) ? 'date' : (in_array($name, $editNumberFields, true) || str_starts_with($name, 'tahun') ? 'number' : 'text') }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $name === 'nomor_perjanjian_f07' ? 'Nomor surat' : ($name === 'judul_kerja_sama_f07' ? 'Judul kerja sama' : '') }}" {{ $requiredF01 || $requiredF02 || $requiredF03 || $requiredF04 || $requiredF05 || $requiredF06 || ($requiredF07 && !$conditionalF07) ? 'required' : '' }} @if($conditionalF07) x-bind:disabled="kerjaSamaBaruF07 !== 'ya'" @endif @if($requiredF07 && $conditionalF07) x-bind:required="kerjaSamaBaruF07 === 'ya'" @endif {{ in_array($kode, ['F.01', 'F.02', 'F.03', 'F.04', 'F.05', 'F.06'], true) && in_array($name, $editNumberFields, true) ? 'min=0 step=1' : '' }} {{ in_array($name, ['tahun_f01', 'tahun_f02', 'tahun_f03', 'tahun_f04', 'tahun_f05', 'tahun_f06', 'tahun_f07'], true) ? 'min=1945 max=2100' : '' }} {{ in_array($name, ['sk_parsial_luas', 'sk_provinsi_luas', 'sk_penetapan_luas', 'panjang_pal_km', 'luas_area_b02', 'luas_area_f07', 'nilai_komitmen_f07'], true) ? 'step=0.01' : '' }} class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                                    @if($name === 'nomor_perjanjian_f07')
                                                        <p class="mt-1 text-xs text-slate-500">Contoh: PKS.123/BKSDA/2024.</p>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                        @if($kode === 'F.07')
                                            <p class="md:col-span-12 rounded-xl border border-slate-200 bg-slate-100 p-5 text-sm leading-relaxed text-slate-600">
                                                <i class="fa-solid fa-circle-info mr-2 text-blue-600"></i>
                                                Data capaian &amp; evaluasi (output, outcome, dampak, estimasi manfaat) <strong>tidak diisi di sini</strong> – diisi belakangan lewat <strong>Evaluasi Tahunan</strong> (per RKT) dan <strong>Capaian Umum</strong> (Detail PKS).
                                            </p>
                                        @endif
                                    </div>
                                    @endif
                                </section>
                            @endforeach
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                            <a href="{{ route('konservasi.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                                <i class="fas fa-xmark"></i> Batal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-800 to-teal-800 hover:from-emerald-900 hover:to-teal-900 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-900/20 transition flex items-center gap-2">
                                <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const form = document.querySelector('form');
        const bidangSelect = document.getElementById('bidang_id');
        const subBidangSelect = document.getElementById('sub_bidang_id');
        const updateSubBidangFields = () => {
            const selectedOption = subBidangSelect.options[subBidangSelect.selectedIndex];
            Alpine.$data(form).selectedSubKode = selectedOption?.dataset.kode || '';
        };

        bidangSelect.addEventListener('change', function () {
            const bidangId = this.value;
            subBidangSelect.innerHTML = '<option value="">-- Memuat Sub-Bidang... --</option>';
            Alpine.$data(form).selectedSubKode = '';

            if (!bidangId) {
                subBidangSelect.innerHTML = '<option value="">-- Pilih Bidang Terlebih Dahulu --</option>';
                return;
            }

            fetch(`/get-sub-bidang/${bidangId}`)
                .then(response => response.json())
                .then(data => {
                    subBidangSelect.innerHTML = '<option value="">-- Pilih Sub-Bidang --</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.dataset.kode = item.kode_sub || item.kode || '';
                        option.textContent = item.nama_sub_bidang;
                        subBidangSelect.appendChild(option);
                    });
                })
                .catch(() => {
                    subBidangSelect.innerHTML = '<option value="">-- Gagal memuat sub-bidang --</option>';
                });
        });

        subBidangSelect.addEventListener('change', updateSubBidangFields);
    </script>
</body>
</html>