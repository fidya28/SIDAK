<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DataKonservasi;
use App\Models\SubBidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KonservasiStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_uses_named_sub_bidang_route_and_shows_validation_errors(): void
    {
        $this->seed(\Database\Seeders\BidangSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::where('nama_bidang', 'Perencanaan Konservasi')->firstOrFail();
        $subBidangUrl = route('konservasi.sub-bidang', ['bidang_id' => '__BIDANG_ID__']);

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee('id="conservation-form"', false)
            ->assertSee('Perencanaan Konservasi')
            ->assertSee("selectedSubBidangKode === 'A.01'", false)
            ->assertSee('action="' . route('logout') . '"', false)
            ->assertSee('Keluar dari akun')
            ->assertSee($subBidangUrl, false)
            ->assertSee('name="keterangan"', false);

        $this->actingAs($user)
            ->get(route('konservasi.sub-bidang', ['bidang_id' => $bidang->id]))
            ->assertOk()
            ->assertJsonFragment([
                'kode_sub' => 'A.01',
                'nama_sub_bidang' => 'Kawasan Konservasi',
            ]);

        $this->from(route('konservasi.create'))
            ->actingAs($user)
            ->post(route('konservasi.store'), [])
            ->assertRedirect(route('konservasi.create'));

        $this->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee('Data belum tersimpan. Periksa kembali isian berikut:')
            ->assertSee('sub bidang id field is required');
    }

    public function test_logout_is_available_in_all_authenticated_navigation_bars(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.01',
            'nama_sub_bidang' => 'Perjumpaan Spesies di dalam dan luar Kawasan Konservasi',
        ]);
        $record = DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $subBidang->id,
            'tahun' => 2026,
            'latitude' => '-0.89',
            'longitude' => '119.87',
            'jumlah' => 3,
            'keterangan' => 'Spesies: Anoa',
        ]);
        $logoutAction = 'action="' . route('logout') . '"';

        $this->actingAs($user);

        foreach ([
            route('konservasi.dashboard'),
            route('konservasi.index'),
            route('konservasi.create'),
            route('konservasi.edit', $record->id),
            route('konservasi.peta'),
            route('landing'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee($logoutAction, false);
        }
    }

    public function test_gis_marker_data_includes_bidang_color_and_sub_bidang_details(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.01',
            'nama_sub_bidang' => 'Perjumpaan Spesies di dalam dan luar Kawasan Konservasi',
        ]);
        DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $subBidang->id,
            'tahun' => 2026,
            'latitude' => '-0.89',
            'longitude' => '119.87',
            'jumlah' => 3,
            'keterangan' => 'Spesies: Anoa | Lokasi: Cagar Alam Dako',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.peta'))
            ->assertOk()
            ->assertSee('Konservasi Spesies dan Genetik')
            ->assertSee('C.01')
            ->assertSee('Perjumpaan Spesies di dalam dan luar Kawasan Konservasi')
            ->assertSee('Spesies: Anoa | Lokasi: Cagar Alam Dako')
            ->assertSee('#e11d48');
    }

    public function test_oversized_sk_parsial_file_shows_limit_and_does_not_save_record(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.01',
            'nama_sub_bidang' => 'Kawasan Konservasi',
        ]);

        $this->actingAs($user)
            ->from(route('konservasi.create'))
            ->post(route('konservasi.store'), [
                'sub_bidang_id' => $subBidang->id,
                'sk_parsial_file' => UploadedFile::fake()->create('sk-parsial.pdf', 11000, 'application/pdf'),
            ])
            ->assertRedirect(route('konservasi.create'));

        $this->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee('Ukuran File SK parsial maksimal 10 MB per file.');

        $this->assertDatabaseCount('data_konservasi', 0);
    }

    public function test_b01_form_values_are_saved_for_the_rekapitulasi(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Kawasan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'B.01',
            'nama_sub_bidang' => 'Kelompok Binaan',
        ]);

        $response = $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun' => '2026',
            'tahun_rpjp' => '2026',
            'tahun_b01' => '2025',
            'periode_semester' => 'Semester II',
            'kawasan_nama_b01' => 'SM Bakiriang',
            'ada_kegiatan_b01' => 'ya',
            'nama_kelompok' => 'Kelompok Hutan Lestari',
            'jumlah_laki' => '7',
            'jumlah_perempuan' => '5',
            'provinsi' => 'Sulawesi Tengah',
            'kabupaten' => 'Banggai',
            'kecamatan' => 'Luwuk',
            'desa' => 'Bubung',
            'jenis_hhbk' => 'Rotan',
            'jenis_bantuan' => 'Peralatan',
            'jumlah_bantuan' => '1500000',
            'sumber_dana' => 'APBN KSDAE',
            'keterangan' => 'Pendampingan tahap kedua',
        ]);

        $response->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2025, $record->tahun);
        $this->assertSame(12, $record->jumlah);
        $this->assertSame(1, substr_count($record->keterangan, 'Tahun:'));
        $this->assertStringNotContainsString('Tahun: 2026', $record->keterangan);
        $this->assertStringContainsString('Periode Semester: Semester II', $record->keterangan);
        $this->assertStringContainsString('Nama Kelompok: Kelompok Hutan Lestari', $record->keterangan);
        $this->assertStringContainsString('Kabupaten: Banggai', $record->keterangan);
        $this->assertStringContainsString('Pemungutan HHBK: Rotan', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Pendampingan tahap kedua', $record->keterangan);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'konservasi_kawasan',
                'sub_bidang' => 'B.01',
            ]))
            ->assertOk()
            ->assertSee('Tahun: 2025')
            ->assertSee('Nama Kelompok: Kelompok Hutan Lestari')
            ->assertSee('Pemungutan HHBK: Rotan');
    }

    public function test_jasa_lingkungan_fields_are_saved_and_rendered_as_matching_columns(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Pemanfaatan Jasa Lingkungan']);
        $pengunjung = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'D.01',
            'nama_sub_bidang' => 'Pengunjung Kawasan Konservasi',
        ]);
        $dampakWisata = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'D.08',
            'nama_sub_bidang' => 'Dampak Aktivitas Wisata Alam',
        ]);
        $arealAir = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'D.10',
            'nama_sub_bidang' => 'Areal Pemanfaatan Air dan Energi Air',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $pengunjung->id,
            'tahun' => '2025',
            'bulan_d01' => '4',
            'kawasan_nama_d01' => 'TWA Wera',
            'penelitian_dalam_negeri' => '12',
            'wisata_alam_luar_negeri' => '3',
        ])->assertRedirect(route('konservasi.index'));

        $pengunjungRecord = DataKonservasi::where('sub_bidang_id', $pengunjung->id)->firstOrFail();
        $this->assertSame(2025, $pengunjungRecord->tahun);
        $this->assertStringContainsString('Kawasan Konservasi: TWA Wera', $pengunjungRecord->keterangan);
        $this->assertStringContainsString('Penelitian dan Pengembangan - Dalam Negeri: 12', $pengunjungRecord->keterangan);

        $this->get(route('konservasi.index', [
            'bidang' => 'pemanfaatan_jasling',
            'sub_bidang' => 'D.01',
        ]))
            ->assertOk()
            ->assertSeeInOrder(['No', 'Tahun', 'Kawasan Konservasi', 'Periode Bulan'])
            ->assertSee('Penelitian dan Pengembangan - Dalam Negeri')
            ->assertSee('Wisata Alam - Luar Negeri')
            ->assertSee('TWA Wera');

        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $dampakWisata->id,
            'tahun_d08' => '2024',
            'periode_bulan_d08' => 'Januari',
            'kawasan_nama_d08' => 'SM Bakiriang',
            'data_tersedia_d08' => 'ya',
            'odtwa_d08' => 'ODTWA 1',
            'dipa_d08' => '2500000',
            'jumlah_pelaku_pemandu_d08' => '4',
            'omset_pemandu_d08' => '900000',
            'nilai_ekonomi_terdampak_d08' => '3000000',
        ])->assertRedirect(route('konservasi.index'));

        $dampakRecord = DataKonservasi::where('sub_bidang_id', $dampakWisata->id)->firstOrFail();
        $this->assertSame(2024, $dampakRecord->tahun);
        $this->assertSame('Januari', $dampakRecord->bulan);
        $this->assertStringContainsString('Jumlah Pelaku Usaha - Pemandu Wisata: 4', $dampakRecord->keterangan);
        $this->assertStringContainsString('Perkiraan Omset - Pemandu Wisata: 900000', $dampakRecord->keterangan);

        $this->get(route('konservasi.index', [
            'bidang' => 'pemanfaatan_jasling',
            'sub_bidang' => 'D.08',
        ]))
            ->assertOk()
            ->assertSee('Perkiraan Omset - Pemandu Wisata')
            ->assertSee('SM Bakiriang');

        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $arealAir->id,
            'tahun_d_10' => '2023',
            'kawasan_nama_d_10' => 'CA Gunung Dako',
            'data_tersedia_d_10' => 'ya',
            'zonasi_blok_d10' => 'Zona Pemanfaatan',
            'bidang_seksi_pengelolaan_wilayah_d10' => 'Seksi Pengelolaan Wilayah I',
        ])->assertRedirect(route('konservasi.index'));

        $arealAirRecord = DataKonservasi::where('sub_bidang_id', $arealAir->id)->firstOrFail();
        $this->assertSame(2023, $arealAirRecord->tahun);
        $this->assertStringContainsString('Zona/Blok: Zona Pemanfaatan', $arealAirRecord->keterangan);

        $this->get(route('konservasi.index', [
            'bidang' => 'pemanfaatan_jasling',
            'sub_bidang' => 'D.10',
        ]))
            ->assertOk()
            ->assertSee('Data Tersedia')
            ->assertSee('Zona/Blok')
            ->assertSee('CA Gunung Dako');
    }

    public function test_species_and_genetics_table_uses_the_conservation_table_layout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.01',
            'nama_sub_bidang' => 'Perjumpaan Spesies di dalam dan luar Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'satuan_kerja_c01' => 'Balai KSDA Sulawesi Tengah',
            'temuan_perjumpaan_spesies_c01' => 'Di dalam kawasan konservasi',
            'tahun_c01' => '2026',
            'bulan_c01' => '8',
            'spesies_c01' => 'Anoa',
            'jumlah_individu_c01' => '2',
        ])->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::where('sub_bidang_id', $subBidang->id)->firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(8, $record->bulan);
        $this->assertStringContainsString('Spesies: Anoa', $record->keterangan);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'konservasi_spesies',
                'sub_bidang' => 'C.01',
            ]))
            ->assertOk()
            ->assertSeeInOrder(['No', 'Tahun', 'Satuan Kerja', 'Periode Bulan', 'Temuan Perjumpaan Spesies', 'Spesies'])
            ->assertSee('Balai KSDA Sulawesi Tengah')
            ->assertSee('Anoa')
            ->assertSee('2026');
    }

    public function test_f01_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.01',
            'nama_sub_bidang' => 'Sebaran PNS/CPNS Menurut Jabatan dan Jenis Kelamin',
        ]);
        $fields = [
            'struktural_i_a_laki_f01', 'struktural_i_a_perempuan_f01',
            'struktural_i_b_laki_f01', 'struktural_i_b_perempuan_f01',
            'struktural_ii_a_laki_f01', 'struktural_ii_a_perempuan_f01',
            'struktural_ii_b_laki_f01', 'struktural_ii_b_perempuan_f01',
            'struktural_iii_a_laki_f01', 'struktural_iii_a_perempuan_f01',
            'struktural_iii_b_laki_f01', 'struktural_iii_b_perempuan_f01',
            'struktural_iv_a_laki_f01', 'struktural_iv_a_perempuan_f01',
            'struktural_iv_b_laki_f01', 'struktural_iv_b_perempuan_f01',
            'fungsional_khusus_laki_f01', 'fungsional_khusus_perempuan_f01',
            'calon_fungsional_khusus_laki_f01', 'calon_fungsional_khusus_perempuan_f01',
            'fungsional_umum_laki_f01', 'fungsional_umum_perempuan_f01',
        ];
        $data = array_merge([
            'sub_bidang_id' => $subBidang->id,
            'tahun_f01' => '2026',
            'satuan_kerja_f01' => 'Balai KSDA Sulawesi Tengah',
            'keterangan' => 'Data kepegawaian tahun 2026',
        ], array_fill_keys($fields, '0'));
        $data['struktural_i_a_laki_f01'] = '3';
        $data['struktural_i_a_perempuan_f01'] = '2';
        $data['fungsional_umum_laki_f01'] = '4';
        $data['fungsional_umum_perempuan_f01'] = '1';

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.01'", false)
            ->assertSee('name="struktural_i_a_laki_f01"', false)
            ->assertSee('name="fungsional_umum_perempuan_f01"', false);

        $this->actingAs($user)
            ->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['fungsional_umum_perempuan_f01' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);
        $this->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode: 'F.01'", false)
            ->assertSee('id="fungsional_umum_laki_f01"', false)
            ->assertSee('value="0"', false);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(10, $record->jumlah);
        $this->assertStringContainsString('Struktural I-A Laki-laki: 3', $record->keterangan);
        $this->assertStringContainsString('Fungsional Umum Perempuan: 1', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data kepegawaian tahun 2026', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('value="3"', false)
            ->assertSee('name="satuan_kerja_f01"', false)
            ->assertSee('name="keterangan"', false);

        $data['struktural_i_a_laki_f01'] = '6';
        $data['bidang_id'] = $bidang->id;
        $data['keterangan'] = 'Pembaruan data kepegawaian';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(13, $record->jumlah);
        $this->assertStringContainsString('Struktural I-A Laki-laki: 6', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Pembaruan data kepegawaian', $record->keterangan);
    }

    public function test_f02_education_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.02',
            'nama_sub_bidang' => 'Sebaran PNS/CPNS Menurut Tingkat Pendidikan dan Jenis Kelamin',
        ]);
        $fields = [
            'doktor_s3_laki_f02', 'doktor_s3_perempuan_f02',
            'master_s2_laki_f02', 'master_s2_perempuan_f02',
            'sarjana_s1_laki_f02', 'sarjana_s1_perempuan_f02',
            'diploma_d3_laki_f02', 'diploma_d3_perempuan_f02',
            'slta_sma_laki_f02', 'slta_sma_perempuan_f02',
            'sltp_smp_laki_f02', 'sltp_smp_perempuan_f02',
            'sekolah_dasar_sd_laki_f02', 'sekolah_dasar_sd_perempuan_f02',
        ];
        $data = array_merge([
            'sub_bidang_id' => $subBidang->id,
            'tahun_f02' => '2026',
            'satuan_kerja_f02' => 'Balai KSDA Sulawesi Tengah',
            'keterangan' => 'Data pendidikan pegawai',
        ], array_fill_keys($fields, '0'));
        $data['doktor_s3_laki_f02'] = '2';
        $data['sarjana_s1_perempuan_f02'] = '5';
        $data['sekolah_dasar_sd_laki_f02'] = '1';

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.02'", false)
            ->assertSee('name="tahun_f02"', false)
            ->assertSee('name="satuan_kerja_f02"', false)
            ->assertSee('name="doktor_s3_laki_f02"', false)
            ->assertSee('name="sekolah_dasar_sd_perempuan_f02"', false);

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['master_s2_perempuan_f02' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(8, $record->jumlah);
        $this->assertStringContainsString('Doktor (S3) Laki-laki: 2', $record->keterangan);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 5', $record->keterangan);
        $this->assertStringContainsString('Sekolah Dasar (SD) Laki-laki: 1', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan pegawai', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="satuan_kerja_f02"', false)
            ->assertSee('name="doktor_s3_laki_f02"', false)
            ->assertSee('value="2"', false)
            ->assertSee('name="keterangan"', false);

        $data['bidang_id'] = $bidang->id;
        $data['sarjana_s1_perempuan_f02'] = '7';
        $data['keterangan'] = 'Data pendidikan diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(10, $record->jumlah);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 7', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan diperbarui', $record->keterangan);
    }

    public function test_f03_grade_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.03',
            'nama_sub_bidang' => 'Sebaran PNS/CPNS Menurut Golongan dan Jenis Kelamin',
        ]);
        $fields = [
            'golongan_iv_laki_f03', 'golongan_iv_perempuan_f03',
            'golongan_iii_laki_f03', 'golongan_iii_perempuan_f03',
            'golongan_ii_laki_f03', 'golongan_ii_perempuan_f03',
            'golongan_i_laki_f03', 'golongan_i_perempuan_f03',
        ];
        $data = array_merge([
            'sub_bidang_id' => $subBidang->id,
            'tahun_f03' => '2026',
            'satuan_kerja_f03' => 'Balai KSDA Sulawesi Tengah',
            'keterangan' => 'Data golongan pegawai',
        ], array_fill_keys($fields, '0'));
        $data['golongan_iv_laki_f03'] = '3';
        $data['golongan_iii_perempuan_f03'] = '6';
        $data['golongan_i_laki_f03'] = '1';

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.03'", false)
            ->assertSee('name="tahun_f03"', false)
            ->assertSee('name="satuan_kerja_f03"', false)
            ->assertSee('name="golongan_iv_laki_f03"', false)
            ->assertSee('name="golongan_i_perempuan_f03"', false);

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['golongan_ii_perempuan_f03' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(10, $record->jumlah);
        $this->assertStringContainsString('Golongan IV Laki-laki: 3', $record->keterangan);
        $this->assertStringContainsString('Golongan III Perempuan: 6', $record->keterangan);
        $this->assertStringContainsString('Golongan I Laki-laki: 1', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data golongan pegawai', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="satuan_kerja_f03"', false)
            ->assertSee('name="golongan_iv_laki_f03"', false)
            ->assertSee('value="3"', false)
            ->assertSee('name="keterangan"', false);

        $data['bidang_id'] = $bidang->id;
        $data['golongan_iii_perempuan_f03'] = '8';
        $data['keterangan'] = 'Data golongan diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(12, $record->jumlah);
        $this->assertStringContainsString('Golongan III Perempuan: 8', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data golongan diperbarui', $record->keterangan);
    }

    public function test_f04_functional_position_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.04',
            'nama_sub_bidang' => 'Sebaran Pejabat Fungsional Tertentu Menurut Fungsi dan Jenis Kelamin',
        ]);
        $data = [
            'sub_bidang_id' => $subBidang->id,
            'tahun_f04' => '2026',
            'satuan_kerja_f04' => 'Balai KSDA Sulawesi Tengah',
            'jenis_jabatan_fungsional_f04' => 'Analis Hukum',
            'laki_f04' => '5',
            'perempuan_f04' => '7',
            'keterangan' => 'Data pejabat fungsional',
        ];

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.04'", false)
            ->assertSee('name="tahun_f04"', false)
            ->assertSee('name="satuan_kerja_f04"', false)
            ->assertSee('name="jenis_jabatan_fungsional_f04"', false)
            ->assertSee('Pengelola Pengadaan Barang/Jasa');

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['jenis_jabatan_fungsional_f04' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $invalidData = $data;
        $invalidData['jenis_jabatan_fungsional_f04'] = 'Jabatan tidak terdaftar';
        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), $invalidData)
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(12, $record->jumlah);
        $this->assertStringContainsString('Jenis Jabatan Fungsional: Analis Hukum', $record->keterangan);
        $this->assertStringContainsString('Laki-laki: 5', $record->keterangan);
        $this->assertStringContainsString('Perempuan: 7', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pejabat fungsional', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="jenis_jabatan_fungsional_f04"', false)
            ->assertSee('value="Analis Hukum" selected', false)
            ->assertSee('name="laki_f04"', false)
            ->assertSee('value="5"', false);

        $data['bidang_id'] = $bidang->id;
        $data['jenis_jabatan_fungsional_f04'] = 'Arsiparis';
        $data['perempuan_f04'] = '8';
        $data['keterangan'] = 'Data pejabat fungsional diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(13, $record->jumlah);
        $this->assertStringContainsString('Jenis Jabatan Fungsional: Arsiparis', $record->keterangan);
        $this->assertStringContainsString('Perempuan: 8', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pejabat fungsional diperbarui', $record->keterangan);
    }

    public function test_f05_functional_position_education_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.05',
            'nama_sub_bidang' => 'Sebaran Pejabat Fungsional Tertentu Menurut Fungsi, Tingkat Pendidikan dan Jenis Kelamin',
        ]);
        $fields = [
            'doktor_s3_laki_f05', 'doktor_s3_perempuan_f05',
            'master_s2_laki_f05', 'master_s2_perempuan_f05',
            'sarjana_s1_laki_f05', 'sarjana_s1_perempuan_f05',
            'diploma_d3_laki_f05', 'diploma_d3_perempuan_f05',
            'slta_sma_laki_f05', 'slta_sma_perempuan_f05',
            'sltp_smp_laki_f05', 'sltp_smp_perempuan_f05',
            'sekolah_dasar_sd_laki_f05', 'sekolah_dasar_sd_perempuan_f05',
        ];
        $data = array_merge([
            'sub_bidang_id' => $subBidang->id,
            'tahun_f05' => '2026',
            'satuan_kerja_f05' => 'Balai KSDA Sulawesi Tengah',
            'jenis_jabatan_fungsional_f05' => 'Analis Hukum',
            'keterangan' => 'Data pendidikan pejabat fungsional',
        ], array_fill_keys($fields, '0'));
        $data['doktor_s3_laki_f05'] = '2';
        $data['sarjana_s1_perempuan_f05'] = '4';
        $data['sekolah_dasar_sd_laki_f05'] = '1';

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.05'", false)
            ->assertSee('name="tahun_f05"', false)
            ->assertSee('name="satuan_kerja_f05"', false)
            ->assertSee('name="jenis_jabatan_fungsional_f05"', false)
            ->assertSee('name="doktor_s3_laki_f05"', false)
            ->assertSee('name="sekolah_dasar_sd_perempuan_f05"', false)
            ->assertSee('Pengelola Pengadaan Barang/Jasa');

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['master_s2_perempuan_f05' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $invalidData = $data;
        $invalidData['jenis_jabatan_fungsional_f05'] = 'Jabatan tidak terdaftar';
        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), $invalidData)
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(7, $record->jumlah);
        $this->assertStringContainsString('Jenis Jabatan Fungsional Tertentu: Analis Hukum', $record->keterangan);
        $this->assertStringContainsString('Doktor (S3) Laki-laki: 2', $record->keterangan);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 4', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan pejabat fungsional', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="jenis_jabatan_fungsional_f05"', false)
            ->assertSee('value="Analis Hukum" selected', false)
            ->assertSee('name="doktor_s3_laki_f05"', false)
            ->assertSee('value="2"', false);

        $data['bidang_id'] = $bidang->id;
        $data['jenis_jabatan_fungsional_f05'] = 'Arsiparis';
        $data['sarjana_s1_perempuan_f05'] = '6';
        $data['keterangan'] = 'Data pendidikan diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(9, $record->jumlah);
        $this->assertStringContainsString('Jenis Jabatan Fungsional Tertentu: Arsiparis', $record->keterangan);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 6', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan diperbarui', $record->keterangan);
    }

    public function test_f06_p3k_education_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.06',
            'nama_sub_bidang' => 'Sebaran ASN P3K menurut Tingkat Pendidikan dan Jenis Kelamin',
        ]);
        $fields = [
            'master_s2_laki_f06', 'master_s2_perempuan_f06',
            'sarjana_s1_laki_f06', 'sarjana_s1_perempuan_f06',
            'diploma_d3_laki_f06', 'diploma_d3_perempuan_f06',
            'slta_sma_laki_f06', 'slta_sma_perempuan_f06',
            'sltp_smp_laki_f06', 'sltp_smp_perempuan_f06',
            'sekolah_dasar_sd_laki_f06', 'sekolah_dasar_sd_perempuan_f06',
        ];
        $data = array_merge([
            'sub_bidang_id' => $subBidang->id,
            'tahun_f06' => '2026',
            'satuan_kerja_f06' => 'Balai KSDA Sulawesi Tengah',
            'keterangan' => 'Data pendidikan ASN P3K',
        ], array_fill_keys($fields, '0'));
        $data['master_s2_laki_f06'] = '2';
        $data['sarjana_s1_perempuan_f06'] = '4';
        $data['sekolah_dasar_sd_laki_f06'] = '1';

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.06'", false)
            ->assertSee('name="tahun_f06"', false)
            ->assertSee('name="satuan_kerja_f06"', false)
            ->assertSee('name="master_s2_laki_f06"', false)
            ->assertSee('name="sekolah_dasar_sd_perempuan_f06"', false)
            ->assertDontSee('name="doktor_s3_laki_f06"', false);

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['master_s2_perempuan_f06' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHas('errors');
        $this->assertDatabaseCount('data_konservasi', 0);

        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(7, $record->jumlah);
        $this->assertStringContainsString('Master (S2) Laki-laki: 2', $record->keterangan);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 4', $record->keterangan);
        $this->assertStringContainsString('Sekolah Dasar (SD) Laki-laki: 1', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan ASN P3K', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="master_s2_laki_f06"', false)
            ->assertSee('value="2"', false)
            ->assertSee('name="keterangan"', false);

        $data['bidang_id'] = $bidang->id;
        $data['sarjana_s1_perempuan_f06'] = '6';
        $data['keterangan'] = 'Data pendidikan ASN P3K diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(9, $record->jumlah);
        $this->assertStringContainsString('Sarjana (S1) Perempuan: 6', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data pendidikan ASN P3K diperbarui', $record->keterangan);
    }

    public function test_f08_permit_counts_are_required_saved_and_editable(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.08',
            'nama_sub_bidang' => 'Perijinan Masuk Kawasan Konservasi',
        ]);

        $countFields = [
            'jumlah_penelitian_dalam_negeri_f08',
            'jumlah_penelitian_luar_negeri_f08',
            'jumlah_pendidikan_dalam_negeri_f08',
            'jumlah_pendidikan_luar_negeri_f08',
            'jumlah_foto_video_dalam_negeri_f08',
            'jumlah_foto_video_luar_negeri_f08',
            'jumlah_lain_lain_dalam_negeri_f08',
            'jumlah_lain_lain_luar_negeri_f08',
        ];
        $data = array_fill_keys($countFields, '0');
        $data = array_merge($data, [
            'bidang_id' => $bidang->id,
            'sub_bidang_id' => $subBidang->id,
            'tahun_f08' => '2026',
            'periode_bulan_f08' => '3',
            'satuan_kerja_f08' => 'Balai KSDA Sulawesi Tengah',
            'jumlah_penelitian_dalam_negeri_f08' => '12',
            'jumlah_penelitian_luar_negeri_f08' => '3',
            'jumlah_pendidikan_dalam_negeri_f08' => '8',
            'jumlah_pendidikan_luar_negeri_f08' => '2',
            'jumlah_foto_video_dalam_negeri_f08' => '5',
            'jumlah_foto_video_luar_negeri_f08' => '1',
            'jumlah_lain_lain_dalam_negeri_f08' => '4',
            'jumlah_lain_lain_luar_negeri_f08' => '2',
            'keterangan' => 'Data perijinan F.08',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.08'", false)
            ->assertSee('name="periode_bulan_f08"', false)
            ->assertSee('PENELITIAN DAN PENGEMBANGAN')
            ->assertSee('PENDIDIKAN DAN ILMU PENGETAHUAN')
            ->assertSee('PENGAMBILAN FOTO DAN VIDEO')
            ->assertSee('name="jumlah_lain_lain_luar_negeri_f08"', false);

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_diff_key($data, ['jumlah_penelitian_dalam_negeri_f08' => true]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHasErrors('jumlah_penelitian_dalam_negeri_f08');

        $this->post(route('konservasi.store'), $data)->assertRedirect(route('konservasi.index'));
        $record = DataKonservasi::where('sub_bidang_id', $subBidang->id)->firstOrFail();
        $this->assertSame(3, (int) $record->bulan);
        $this->assertSame(37, (int) $record->jumlah);
        $this->assertStringContainsString('Penelitian dan Pengembangan - Dalam Negeri: 12', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data perijinan F.08', $record->keterangan);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="periode_bulan_f08"', false)
            ->assertSee('value="12"', false)
            ->assertSee('Dalam Negeri:')
            ->assertSee('Data perijinan F.08');

        $data['periode_bulan_f08'] = '4';
        $data['jumlah_penelitian_dalam_negeri_f08'] = '10';
        $data['keterangan'] = 'Data perijinan F.08 diperbarui';
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));
        $record->refresh();
        $this->assertSame(4, (int) $record->bulan);
        $this->assertSame(35, (int) $record->jumlah);
        $this->assertStringContainsString('Penelitian dan Pengembangan - Dalam Negeri: 10', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Data perijinan F.08 diperbarui', $record->keterangan);
    }

    public function test_f07_cooperation_supports_nihil_multi_select_uploads_and_editing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Kesekretariatan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'F.07',
            'nama_sub_bidang' => 'Kerja Sama Teknik Bidang KSDAE',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.create'))
            ->assertOk()
            ->assertSee("selectedSubBidangKode === 'F.07'", false)
            ->assertSee('name="provinsi_f07[]"', false)
            ->assertSee('name="dukungan_klhk_f07[]"', false)
            ->assertSee('x-for="option in selected"', false)
            ->assertSee('selected = selected.filter((item) => item !== option)', false)
            ->assertSee('&times;', false)
            ->assertSee('@focus="open = true"', false)
            ->assertSee('x-show="open"', false)
            ->assertSee('name="status_kerja_sama_f07"', false)
            ->assertDontSee('<select id="status_kerja_sama_f07"', false)
            ->assertSee('Status Kerja Sama:', false)
            ->assertSee('Contoh: PKS.123/BKSDA/2024.')
            ->assertSee('Cari IKSS / IKP / IKK...')
            ->assertSee('Pilih indikator dari referensi (terstruktur, boleh lebih dari satu).')
            ->assertSee('Catatan Dukungan Kinerja (teks bebas):')
            ->assertSee('ESRI Shapefile terdiri dari file dengan ekstensi .shp, .dbf, .prj, .shx, dan .cpg')
            ->assertSee('Data capaian &amp; evaluasi', false)
            ->assertSee('Papua Barat Daya')
            ->assertSee('Badan Usaha Milik Daerah')
            ->assertSee('IKK-2.1.2');

        $baseData = [
            'sub_bidang_id' => $subBidang->id,
            'tahun_f07' => '2026',
            'periode_semester_f07' => 'Semester I',
            'satuan_kerja_f07' => 'Balai KSDA Sulawesi Tengah',
            'perjanjian_baru_f07' => 'tidak',
            'keterangan' => 'Tidak ada perjanjian baru pada semester ini',
        ];
        $this->post(route('konservasi.store'), $baseData)
            ->assertRedirect(route('konservasi.index'));
        $nihil = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $nihil->tahun);
        $this->assertStringContainsString('Perjanjian Kerja Sama Baru: tidak', $nihil->keterangan);

        $data = array_merge($baseData, [
            'perjanjian_baru_f07' => 'ya',
            'jenis_dokumen_f07' => 'PKS',
            'nama_mitra_f07' => 'Mitra Uji',
            'tipe_mitra_f07' => 'Badan Usaha Milik Negara',
            'status_kerja_sama_f07' => 'Aktif',
            'nomor_perjanjian_f07' => 'PKS/001/2026',
            'tanggal_mulai_f07' => '2026-01-01',
            'tanggal_berakhir_f07' => '2027-01-01',
            'judul_kerja_sama_f07' => 'Konservasi habitat',
            'ruang_lingkup_f07' => 'Perlindungan habitat satwa liar',
            'dukungan_klhk_f07' => [
                'IKK-2.1.2 — Persentase entitas kerja sama yang efektif, efisien, transparan, dan akuntabel',
                'IKK-PROV-AREAL-PRESERVASI',
            ],
            'catatan_dukungan_f07' => 'Dukungan teknis dan pemantauan',
            'luas_area_f07' => '12.5',
            'nilai_komitmen_f07' => '1500000',
            'provinsi_f07' => ['Aceh', 'Sulawesi Tengah'],
            'lokasi_detail_f07' => 'Kabupaten Donggala',
            'dokumen_kerja_sama_f07' => UploadedFile::fake()->create('pks.pdf', 100, 'application/pdf'),
            'shapefile_f07' => UploadedFile::fake()->createWithContent('lokasi.zip', "PK\x03\x04"),
        ]);

        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_merge($data, ['provinsi_f07' => ['Provinsi Tidak Ada']]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHasErrors('provinsi_f07.0');
        $this->from(route('konservasi.create'))
            ->post(route('konservasi.store'), array_merge($data, [
                'dukungan_klhk_f07' => ['Indikator Tidak Ada'],
            ]))
            ->assertRedirect(route('konservasi.create'))
            ->assertSessionHasErrors('dukungan_klhk_f07.0');
        $this->post(route('konservasi.store'), $data)
            ->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::where('sub_bidang_id', $subBidang->id)->latest('id')->firstOrFail();
        $this->assertStringContainsString('Tipe Mitra: Badan Usaha Milik Negara', $record->keterangan);
        $this->assertStringContainsString('Provinsi: ["Aceh","Sulawesi Tengah"]', $record->keterangan);
        $this->assertStringContainsString('Dukungan Kinerja KLHK (referensi): ["IKK-2.1.2', $record->keterangan);
        $this->assertStringContainsString('Luas Area: 12.5', $record->keterangan);
        $this->assertStringContainsString('Nilai Komitmen Pendanaan: 1500000', $record->keterangan);
        $this->assertStringContainsString('Dokumen Kerja Sama (pdf): dokumen_kerjasama/', $record->keterangan);
        $this->assertStringContainsString('Unggah Point/Polygon Area (shapefile): shapefiles/', $record->keterangan);
        $uploadedFiles = Storage::disk('public')->allFiles();
        $this->assertCount(2, $uploadedFiles);

        $this->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('name="dukungan_klhk_f07[]"', false)
            ->assertSee('name="provinsi_f07[]"', false)
            ->assertSee('x-for="option in selected"', false)
            ->assertSee('selected = selected.filter((item) => item !== option)', false)
            ->assertSee('&times;', false)
            ->assertSee('@focus="open = true"', false)
            ->assertSee('x-show="open"', false)
            ->assertSee('role="group" aria-label="Status Kerja Sama"', false)
            ->assertSee('INFORMASI KERJA SAMA')
            ->assertSee('Contoh: PKS.123/BKSDA/2024.')
            ->assertSee('Mitra Uji')
            ->assertSee('Sulawesi Tengah');

        unset($data['dokumen_kerja_sama_f07'], $data['shapefile_f07']);
        $data['bidang_id'] = $bidang->id;
        $data['provinsi_f07'] = ['Bali', 'Sulawesi Tengah'];
        $this->put(route('konservasi.update', $record->id), $data)
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertStringContainsString('Provinsi: ["Bali","Sulawesi Tengah"]', $record->keterangan);
        $this->assertStringContainsString('Dokumen Kerja Sama (pdf): dokumen_kerjasama/', $record->keterangan);
        foreach ($uploadedFiles as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->put(route('konservasi.update', $record->id), array_merge($baseData, [
            'bidang_id' => $bidang->id,
        ]))->assertRedirect(route('konservasi.index'));
        $record->refresh();
        $this->assertStringContainsString('Perjanjian Kerja Sama Baru: tidak', $record->keterangan);
        $this->assertStringNotContainsString('Mitra Kerja Sama:', $record->keterangan);
        $this->assertStringNotContainsString('Dokumen Kerja Sama:', $record->keterangan);
    }

    public function test_a01_rekap_shows_columns_matching_the_kawasan_konservasi_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.01',
            'nama_sub_bidang' => 'Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun' => '2025',
            'kawasan_nama' => 'SM Bakiriang',
            'ada_perubahan' => 'ya',
            'sk_parsial_nomor' => '123/2025',
            'sk_parsial_tanggal' => '2025-06-15',
            'sk_parsial_luas' => '15.25',
            'sk_provinsi_tersedia' => 'ya',
            'sk_provinsi_nomor' => '456/2025',
            'sk_provinsi_tanggal' => '2025-07-10',
            'sk_provinsi_luas' => '120.50',
            'sk_penetapan_tersedia' => 'tidak',
            'keterangan' => 'Pembaruan batas kawasan',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.01',
            ]))
            ->assertOk()
            ->assertSee('SK Penunjukan Parsial')
            ->assertSee('SK Penunjukan Provinsi')
            ->assertSee('SK Penetapan')
            ->assertSee('Batas Geografis Kawasan')
            ->assertSee('SM Bakiriang')
            ->assertSee('123/2025')
            ->assertSee('15.25 Ha')
            ->assertSee('Tersedia')
            ->assertSee('Tidak tersedia')
            ->assertSee('Pembaruan batas kawasan');
    }

    public function test_a02_rekap_shows_rpjp_fields_from_the_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.02',
            'nama_sub_bidang' => 'Perencanaan Pengelolaan Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_rpjp' => '2025',
            'kawasan_nama_rpjp' => 'SM Bakiriang',
            'ketersediaan_rpjp' => 'ya',
            'sk_rpjp_nomor' => 'SK-RPJP-2025/01',
            'sk_rpjp_tanggal_pengesahan' => '2025-04-12',
            'sk_rpjp_periode_berakhir' => '2045-12-31',
            'keterangan' => 'Dokumen telah diperbarui',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.02',
            ]))
            ->assertOk()
            ->assertSee('SK Penetapan Dokumen RPJP')
            ->assertSee('Nomor Surat Keputusan')
            ->assertSee('Tanggal Pengesahan')
            ->assertSee('Periode Berakhir RPJ Panjang')
            ->assertSee('SM Bakiriang')
            ->assertSee('Tersedia')
            ->assertSee('SK-RPJP-2025/01')
            ->assertSee('2025-04-12')
            ->assertSee('2045-12-31')
            ->assertSee('Dokumen telah diperbarui');
    }

    public function test_a03_rekap_shows_monitoring_boundary_fields_from_the_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.03',
            'nama_sub_bidang' => 'Monitoring Batas Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_monitoring' => '2026',
            'kawasan_nama_monitoring' => 'CA Gunung Tinombala',
            'ada_kegiatan_monitoring' => 'ya',
            'jenis_kegiatan' => 'Rekonstruksi',
            'nomor_batb' => 'BATB-03/2026',
            'tanggal_batb' => '2026-09-27',
            'pal_baik' => '8',
            'pal_rusak' => '2',
            'pal_hilang' => '1',
            'pal_total' => '11',
            'panjang_pal_km' => '23.75',
            'keterangan' => 'Pemantauan selesai',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.03',
            ]))
            ->assertOk()
            ->assertSee('Berita Acara Tata Batas (BATB)')
            ->assertSee('Jenis Kegiatan')
            ->assertSee('Pal Baik')
            ->assertSee('Lokasi Geografis Hasil Kegiatan')
            ->assertSee('CA Gunung Tinombala')
            ->assertSee('Rekonstruksi')
            ->assertSee('BATB-03/2026')
            ->assertSee('23.75 Km')
            ->assertSee('Pemantauan selesai');
    }

    public function test_a04_rekap_shows_function_evaluation_fields_from_the_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.04',
            'nama_sub_bidang' => 'Hasil Evaluasi Kesesuaian Fungsi Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_evaluasi' => '2026',
            'kawasan_nama_evaluasi' => 'TWA Wera',
            'ketersediaan_evaluasi' => 'ya',
            'tanggal_pelaksanaan_evaluasi' => '2026-08-14',
            'rekomendasi_evaluasi' => 'Pertahankan fungsi kawasan',
            'tindak_lanjut_evaluasi' => 'Laksanakan pemulihan habitat',
            'keterangan' => 'Evaluasi tahap pertama',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.04',
            ]))
            ->assertOk()
            ->assertSee('Tanggal Pelaksanaan')
            ->assertSee('Rekomendasi')
            ->assertSee('Tindak Lanjut')
            ->assertSee('File Dokumen')
            ->assertSee('TWA Wera')
            ->assertSee('Tersedia')
            ->assertSee('2026-08-14')
            ->assertSee('Pertahankan fungsi kawasan')
            ->assertSee('Laksanakan pemulihan habitat')
            ->assertSee('Evaluasi tahap pertama');
    }

    public function test_a05_rekap_shows_ecosystem_data_and_uploaded_shapefile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.05',
            'nama_sub_bidang' => 'Ekosistem Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_ekosistem' => '2026',
            'kawasan_nama_ekosistem' => 'TWA Wera',
            'ketersediaan_ekosistem' => 'ya',
            'shapefile_ekosistem_zip' => UploadedFile::fake()->createWithContent('ekosistem.zip', "PK\x03\x04"),
            'keterangan' => 'Pemetaan ekosistem terbaru',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.05',
            ]))
            ->assertOk()
            ->assertSee('Shapefile Area (ZIP)')
            ->assertSee('Ketersediaan Data')
            ->assertSee('TWA Wera')
            ->assertSee('Tersedia')
            ->assertSee('Lihat shapefile')
            ->assertSee('Pemetaan ekosistem terbaru')
            ->assertSee('Ketersediaan Ekosistem: ya')
            ->assertSee('x-text="detail.label"', false)
            ->assertSee('x-text="detail.value"', false)
            ->assertSee('Belum ada rincian untuk data ini.');
    }

    public function test_a06_rekap_shows_zoning_fields_and_uploaded_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'A.06',
            'nama_sub_bidang' => 'Penataan Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_zonasi' => '2026',
            'kawasan_nama_zonasi' => 'TWA Wera',
            'ketersediaan_zonasi' => 'sudah',
            'nomor_sk_zonasi' => 'SK-ZONASI-06/2026',
            'tanggal_sk_zonasi' => '2026-05-18',
            'file_sk_zonasi' => UploadedFile::fake()->create('zonasi.pdf', 1, 'application/pdf'),
            'shapefile_zonasi_zip' => UploadedFile::fake()->createWithContent('zonasi.zip', "PK\x03\x04"),
            'keterangan' => 'Zonasi telah ditetapkan',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'perencanaan_konservasi',
                'sub_bidang' => 'A.06',
            ]))
            ->assertOk()
            ->assertSee('Ketersediaan Data Zonasi/Blok')
            ->assertSee('Shapefile Area (ZIP)')
            ->assertSee('TWA Wera')
            ->assertSee('Sudah')
            ->assertSee('SK-ZONASI-06/2026')
            ->assertSee('2026-05-18')
            ->assertSee('Lihat file SK')
            ->assertSee('Lihat shapefile')
            ->assertSee('Zonasi telah ditetapkan');
    }

    public function test_edit_prefills_and_updates_the_selected_sub_bidang_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Kawasan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'B.01',
            'nama_sub_bidang' => 'Kelompok Binaan',
        ]);
        $record = DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $subBidang->id,
            'tahun' => 2025,
            'bulan' => 3,
            'latitude' => '-0.89',
            'longitude' => '119.87',
            'keterangan' => 'Tahun: 2025 | Periode Semester: Semester I | Kawasan Konservasi: SM Bakiriang | Nama Kelompok: Kelompok Lama | Jumlah Laki-laki: 5 | Jumlah Perempuan: 4 | Keterangan: Catatan lama',
        ]);

        $this->actingAs($user)
            ->get(route('konservasi.edit', $record->id))
            ->assertOk()
            ->assertSee('Data Sub-Bidang B.01')
            ->assertSee('value="Kelompok Lama"', false)
            ->assertSee('value="Semester I"', false)
            ->assertDontSee('Waktu & Koordinat Spasial')
            ->assertDontSee('Keterangan Tambahan')
            ->assertDontSee('name="latitude"', false)
            ->assertDontSee('name="longitude"', false)
            ->assertDontSee('name="bulan"', false);

        $this->actingAs($user)
            ->put(route('konservasi.update', $record->id), [
                'bidang_id' => $bidang->id,
                'sub_bidang_id' => $subBidang->id,
                'tahun_b01' => '2026',
                'periode_semester' => 'Semester II',
                'kawasan_nama_b01' => 'SM Bakiriang',
                'nama_kelompok' => 'Kelompok Baru',
                'jumlah_laki' => '6',
                'jumlah_perempuan' => '4',
            ])
            ->assertRedirect(route('konservasi.index'));

        $record->refresh();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame(10, $record->jumlah);
        $this->assertSame(3, $record->bulan);
        $this->assertSame('-0.89', $record->latitude);
        $this->assertSame('119.87', $record->longitude);
        $this->assertStringContainsString('Nama Kelompok: Kelompok Baru', $record->keterangan);
        $this->assertStringContainsString('Periode Semester: Semester II', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Catatan lama', $record->keterangan);
    }

    public function test_d09_coordinates_are_saved_for_the_gis_map(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Pemanfaatan Jasa Lingkungan']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'D.09',
            'nama_sub_bidang' => 'Potensi Pemanfaatan Air dan Energi Air di Kawasan Konservasi',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_d09' => '2026',
            'kawasan_nama_d09' => 'TWA Wera',
            'latitude_air_d09' => '-0.897123',
            'longitude_air_d09' => '119.87123',
        ])->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2026, $record->tahun);
        $this->assertSame('-0.897123', $record->latitude);
        $this->assertSame('119.87123', $record->longitude);

        $this->actingAs($user)
            ->get(route('konservasi.peta'))
            ->assertOk()
            ->assertSee('-0.897123')
            ->assertSee('119.87123');

        $this->actingAs($user)
            ->get(route('konservasi.index', [
                'bidang' => 'pemanfaatan_jasling',
                'sub_bidang' => 'D.09',
            ]))
            ->assertOk()
            ->assertSee('Latitude')
            ->assertSee('Longitude')
            ->assertSee('TWA Wera')
            ->assertSee('-0.897123')
            ->assertSee('119.87123');
    }

    public function test_c15_release_fields_are_saved_with_period_count_and_coordinates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.15',
            'nama_sub_bidang' => 'Rekapitulasi Pelepasliaran Kembali Satwa',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_c15' => '2025',
            'bulan_c15' => '9',
            'satuan_kerja_c15' => 'Balai KSDA Sulawesi Tengah',
            'ada_pelepasan_satwa_c15' => 'ya',
            'spesies_c15' => 'Maleo',
            'jumlah_individu_c15' => '2',
            'asal_usul_c15' => 'Hasil Penyelamatan Satwa',
            'longitude_c15' => '119.87123',
            'latitude_c15' => '-0.897123',
            'keterangan_c15' => 'Pelepasliaran di habitat alami',
            'keterangan' => 'Catatan umum duplikat',
        ])->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2025, $record->tahun);
        $this->assertSame(9, $record->bulan);
        $this->assertSame(2, $record->jumlah);
        $this->assertSame('-0.897123', $record->latitude);
        $this->assertSame('119.87123', $record->longitude);
        $this->assertStringContainsString('Spesies yang Dilepasliarkan: Maleo', $record->keterangan);
        $this->assertStringContainsString('Asal-usul: Hasil Penyelamatan Satwa', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Pelepasliaran di habitat alami', $record->keterangan);
        $this->assertSame(1, substr_count($record->keterangan, 'Keterangan:'));
        $this->assertStringNotContainsString('Catatan umum duplikat', $record->keterangan);
    }

    public function test_c16_birth_fields_are_saved_with_period_count_and_coordinates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.16',
            'nama_sub_bidang' => 'Rekapitulasi Kelahiran Satwa',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_c16' => '2025',
            'bulan_c16' => '9',
            'satuan_kerja_c16' => 'Balai KSDA Sulawesi Tengah',
            'ada_kelahiran_satwa_c16' => 'ya',
            'spesies_c16' => 'Maleo',
            'tanggal_kelahiran_c16' => '2025-09-12',
            'usulan_nama_satwa_c16' => 'Bintang',
            'jantan_c16' => '1',
            'betina_c16' => '2',
            'belum_diketahui_c16' => '0',
            'longitude_c16' => '119.87123',
            'latitude_c16' => '-0.897123',
            'keterangan_c16' => 'Kelahiran di habitat alami',
            'keterangan' => 'Catatan umum duplikat',
        ])->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2025, $record->tahun);
        $this->assertSame(9, $record->bulan);
        $this->assertSame(3, $record->jumlah);
        $this->assertSame('-0.897123', $record->latitude);
        $this->assertSame('119.87123', $record->longitude);
        $this->assertStringContainsString('Spesies Satwa: Maleo', $record->keterangan);
        $this->assertStringContainsString('Tanggal Kelahiran: 2025-09-12', $record->keterangan);
        $this->assertStringContainsString('Usulan Nama Satwa: Bintang', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Kelahiran di habitat alami', $record->keterangan);
        $this->assertSame(1, substr_count($record->keterangan, 'Keterangan:'));
        $this->assertStringNotContainsString('Catatan umum duplikat', $record->keterangan);
    }

    public function test_c17_death_fields_are_saved_with_period_count_and_coordinates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $subBidang = SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => 'C.17',
            'nama_sub_bidang' => 'Rekapitulasi Kematian Satwa Liar',
        ]);

        $this->actingAs($user)->post(route('konservasi.store'), [
            'sub_bidang_id' => $subBidang->id,
            'tahun_c17' => '2025',
            'bulan_c17' => '9',
            'satuan_kerja_c17' => 'Balai KSDA Sulawesi Tengah',
            'ada_kematian_satwa_c17' => 'ya',
            'spesies_c17' => 'Maleo',
            'tanggal_kematian_c17' => '2025-09-12',
            'perkiraan_usia_c17' => 'Dewasa',
            'penyebab_kematian_c17' => 'Sakit/Penyakit',
            'jantan_c17' => '1',
            'betina_c17' => '2',
            'belum_diketahui_c17' => '0',
            'upaya_penanganan_c17' => 'Pemeriksaan dan penanganan satwa',
            'longitude_c17' => '119.87123',
            'latitude_c17' => '-0.897123',
            'keterangan_c17' => 'Kematian tercatat di habitat alami',
            'keterangan' => 'Catatan umum duplikat',
        ])->assertRedirect(route('konservasi.index'));

        $record = DataKonservasi::firstOrFail();
        $this->assertSame(2025, $record->tahun);
        $this->assertSame(9, $record->bulan);
        $this->assertSame(3, $record->jumlah);
        $this->assertSame('-0.897123', $record->latitude);
        $this->assertSame('119.87123', $record->longitude);
        $this->assertStringContainsString('Spesies Satwa: Maleo', $record->keterangan);
        $this->assertStringContainsString('Tanggal Kematian: 2025-09-12', $record->keterangan);
        $this->assertStringContainsString('Perkiraan Usia: Dewasa', $record->keterangan);
        $this->assertStringContainsString('Penyebab Kematian: Sakit/Penyakit', $record->keterangan);
        $this->assertStringContainsString('Keterangan: Kematian tercatat di habitat alami', $record->keterangan);
        $this->assertSame(1, substr_count($record->keterangan, 'Keterangan:'));
        $this->assertStringNotContainsString('Catatan umum duplikat', $record->keterangan);
    }

    public function test_file_uploads_are_stored_for_pdf_zip_and_image_fields(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $bidang = Bidang::create(['nama_bidang' => 'Konservasi Spesies dan Genetik']);
        $createSubBidang = fn (string $kode) => SubBidang::create([
            'bidang_id' => $bidang->id,
            'kode_sub' => $kode,
            'nama_sub_bidang' => $kode,
        ]);

        $this->actingAs($user);

        $odtwa = $createSubBidang('D.04');
        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $odtwa->id,
            'foto_odtwa' => UploadedFile::fake()->image('odtwa.jpg'),
        ])->assertRedirect(route('konservasi.index'));
        $this->assertCount(1, Storage::disk('public')->allFiles('foto_odtwa'));

        $izin = $createSubBidang('C.02');
        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $izin->id,
            'dokumen_perizinan_c02' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('konservasi.index'));
        $this->assertCount(1, Storage::disk('public')->allFiles('dokumen_perizinan'));

        $shapefile = $createSubBidang('D.10');
        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $shapefile->id,
            'shapefile_d10' => UploadedFile::fake()->create('area.zip', 100, 'application/zip'),
        ])->assertRedirect(route('konservasi.index'));
        $this->assertCount(1, Storage::disk('public')->allFiles('shapefiles'));

        $sarana = $createSubBidang('D.07');
        $this->post(route('konservasi.store'), [
            'sub_bidang_id' => $sarana->id,
            'foto_sarana_prasarana_d07' => [
                UploadedFile::fake()->image('sarana-1.jpg'),
                UploadedFile::fake()->image('sarana-2.jpg'),
            ],
        ])->assertRedirect(route('konservasi.index'));
        $this->assertCount(2, Storage::disk('public')->allFiles('foto_sarana_prasarana'));
    }

}