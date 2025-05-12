<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NotaDinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        $subKegiatanIds = DB::table('sub_kegiatans')->pluck('id')->toArray();

        foreach ($subKegiatanIds as $subKegiatanId) {
            for ($i = 1; $i <= 2; $i++) {
                $nomorNota = 'ND-' . $subKegiatanId . '-' . $i . '-' . $faker->unique()->randomNumber(4);
                $perihal = "Perihal Nota Dinas " . $i . " untuk Sub Kegiatan " . $subKegiatanId;
                $anggaran = $faker->numberBetween(1000000, 50000000);
                $tanggalPengajuan = $faker->date();

                DB::table('nota_dinas')->insert([
                    'nomor_nota' => $nomorNota,
                    'perihal' => $perihal,
                    'anggaran' => $anggaran,
                    'tanggal_pengajuan' => $tanggalPengajuan,
                    'sub_kegiatan_id' => $subKegiatanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
