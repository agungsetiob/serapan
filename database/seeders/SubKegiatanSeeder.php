<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SubKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        $kegiatanIds = DB::table('kegiatans')->pluck('id')->toArray();

        foreach ($kegiatanIds as $kegiatanId) {
            for ($i = 1; $i <= 2; $i++) {
                $namaSubKegiatan = "Sub Kegiatan " . $i . " - Kegiatan Tanah Bumbu BERAKSI" . $kegiatanId;
                $pagu = 0;
                $tahunAnggaran = 2025;
                $totalSerapan = 0;
                $presentaseSerapan = 0;

                DB::table('sub_kegiatans')->insert([
                    'kegiatan_id' => $kegiatanId,
                    'kode_rekening' => $faker->regexify('[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{2}'),
                    'nama' => $namaSubKegiatan,
                    'pagu' => $pagu,
                    'tahun_anggaran' => $tahunAnggaran,
                    'total_serapan' => $totalSerapan,
                    'presentase_serapan' => $presentaseSerapan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
