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
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($skpdId = 1; $skpdId <= 13; $skpdId++) {
            $skpdProgramIds = DB::table('programs')
                                ->where('skpd_id', $skpdId)
                                ->where('tahun_anggaran', 2025)
                                ->pluck('id')
                                ->toArray();

            if (empty($skpdProgramIds)) {
                continue;
            }

            foreach ($skpdProgramIds as $programId) {
                for ($i = 1; $i <= 2; $i++) {
                    $namaKegiatan = $faker->regexify('[0-9]{2}\.[0-9]{2}\.[0-9]{2}') . " - Kegiatan " . $i . " - Program " . $programId;
                    $pagu = 0;
                    $tahunAnggaran = 2025;
                    $totalSerapan = 0;
                    $presentaseSerapan = 0;

                    DB::table('kegiatans')->insert([
                        'skpd_id' => $skpdId,
                        'program_id' => $programId,
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
}
