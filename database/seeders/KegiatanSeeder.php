<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        for ($skpdId = 1; $skpdId <= 13; $skpdId++) {
            for ($i = 1; $i <= 3; $i++) {
                $namaKegiatan = $faker->regexify('[0-9]{2}\.[0-9]{2}\.[0-9]{2}') . " - Kegiatan " . $i . " - SKPD " . $skpdId;
                $pagu = 0;
                $tahunAnggaran = 2025;
                $totalSerapan = 0;
                $presentaseSerapan = 0;
                $presentaseSerapan = round($presentaseSerapan, 2);

                // Masukkan data ke dalam tabel 'kegiatans'
                DB::table('kegiatans')->insert([
                    'skpd_id' => $skpdId,
                    'nama' => $namaKegiatan,
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
