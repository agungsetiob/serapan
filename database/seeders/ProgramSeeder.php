<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProgramSeeder extends Seeder
{
    /**
     * Jalankan seed database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        for ($skpdId = 1; $skpdId <= 71; $skpdId++) {
            for ($i = 1; $i <= 2; $i++) {
                $namaProgram = $faker->regexify('[A-Z]{3}-[0-9]{3}') . " - Program Unggulan Tanah Bumbu BERAKSI" . $faker->word() . " SKPD " . $skpdId;

                DB::table('programs')->insert([
                    'skpd_id' => $skpdId,
                    'nama' => $namaProgram,
                    'tahun_anggaran' => 2025,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
