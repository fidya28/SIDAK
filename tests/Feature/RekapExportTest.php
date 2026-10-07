<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DataKonservasi;
use App\Models\SubBidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekap_shows_keyword_search_before_a_sub_bidang_is_selected(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('rekap.index'))
            ->assertOk()
            ->assertSee('Pilih Kategori Data Konservasi')
            ->assertSee('name="search"', false)
            ->assertSee('Bidang, sub-bidang, tahun, atau detail data')
            ->assertDontSee('Hasil Pencarian');
    }

    public function test_global_keyword_search_matches_bidang_sub_bidang_year_and_dynamic_details(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $perencanaan = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $kawasan = Bidang::create(['nama_bidang' => 'Konservasi Kawasan']);
        $a01 = SubBidang::create([
            'bidang_id' => $perencanaan->id,
            'kode_sub' => 'A.01',
            'nama_sub_bidang' => 'Kawasan Konservasi',
        ]);
        $b01 = SubBidang::create([
            'bidang_id' => $kawasan->id,
            'kode_sub' => 'B.01',
            'nama_sub_bidang' => 'Kelompok Binaan',
        ]);

        DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $a01->id,
            'tahun' => 2026,
            'keterangan' => 'Nama Kawasan: Cagar Alam Morowali | Status: Aktif',
        ]);
        DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $b01->id,
            'tahun' => 2025,
            'keterangan' => 'Nama Kelompok: Rotan Lestari | Jenis HHBK: Rotan',
        ]);

        $this->actingAs($user)
            ->get(route('rekap.index', ['search' => 'Perencanaan A.01 2026 Morowali']))
            ->assertOk()
            ->assertSee('Hasil Pencarian')
            ->assertSee('Kawasan Konservasi')
            ->assertSee('Cagar Alam Morowali')
            ->assertDontSee('Rotan Lestari');

        $this->get(route('rekap.index', ['search' => 'Konservasi Kawasan B.01 2025 Rotan Lestari']))
            ->assertOk()
            ->assertSee('Rotan Lestari')
            ->assertSee('Jenis HHBK')
            ->assertDontSee('Cagar Alam Morowali');
    }

    public function test_exports_only_the_selected_sub_bidang_and_include_all_rows(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $perencanaan = Bidang::create(['nama_bidang' => 'Perencanaan Konservasi']);
        $kawasan = Bidang::create(['nama_bidang' => 'Konservasi Kawasan']);
        $a01 = SubBidang::create([
            'bidang_id' => $perencanaan->id,
            'kode_sub' => 'A.01',
            'nama_sub_bidang' => 'Kawasan Konservasi',
        ]);
        $b01 = SubBidang::create([
            'bidang_id' => $kawasan->id,
            'kode_sub' => 'B.01',
            'nama_sub_bidang' => 'Kelompok Binaan',
        ]);

        foreach (range(1, 11) as $number) {
            DataKonservasi::create([
                'user_id' => $user->id,
                'sub_bidang_id' => $a01->id,
                'tahun' => 2026,
                'keterangan' => 'Kawasan Konservasi: A01 Record ' . $number,
            ]);
        }

        DataKonservasi::create([
            'user_id' => $user->id,
            'sub_bidang_id' => $b01->id,
            'tahun' => 2026,
            'keterangan' => 'Nama Kelompok: B01 Secret Record',
        ]);

        $this->actingAs($user)
            ->get(route('rekap.index', ['sub_bidang' => 'A.01']))
            ->assertOk()
            ->assertSee('SK Penunjukan Parsial')
            ->assertSee('name="search"', false)
            ->assertSee('A01 Record 1')
            ->assertDontSee('B01 Secret Record');

        $this->get(route('rekap.index', [
            'bidang' => 'perencanaan_konservasi',
            'sub_bidang' => 'A.01',
            'search' => 'A01 Record 11',
        ]))
            ->assertOk()
            ->assertSee('A01 Record 11')
            ->assertDontSee('A01 Record 10')
            ->assertDontSee('B01 Secret Record');

        $excelA01 = $this->actingAs($user)->get(route('konservasi.export.excel', [
            'bidang' => 'perencanaan_konservasi',
            'sub_bidang' => 'A.01',
        ]));

        $excelA01->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('Kawasan Konservasi')
            ->assertSee('A01 Record 11')
            ->assertDontSee('B01 Secret Record');

        $excelB01 = $this->get(route('konservasi.export.excel', [
            'bidang' => 'konservasi_kawasan',
            'sub_bidang' => 'B.01',
        ]));

        $excelB01->assertOk()
            ->assertSee('Nama Kelompok')
            ->assertSee('B01 Secret Record')
            ->assertDontSee('A01 Record 1');

        $pdf = $this->get(route('konservasi.export.pdf', [
            'bidang' => 'perencanaan_konservasi',
            'sub_bidang' => 'A.01',
        ]));

        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }
}