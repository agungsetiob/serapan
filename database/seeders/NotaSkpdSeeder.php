<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class NotaSkpdSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $skpds = DB::table('skpds')->get();
        $userIds = DB::table('users')->pluck('id')->toArray();

        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        foreach ($skpds as $skpd) {
            for ($i = 1; $i <= 10; $i++) {
                $tanggal = Carbon::createFromDate(now()->year, 1, 1)->addDays(rand(0, now()->dayOfYear - 1));
                $bulan = (int)$tanggal->format('n');
                $tahun = $tanggal->format('Y');
                $bulanRom = $bulanRomawi[$bulan];

                $nomor = sprintf('B/000.1.2.3/%04d/%s/%s/%s/TANBU', 
                    rand(1000, 9999),
                    'RSUD-ADMKEU.1',
                    $bulanRom,
                    $tahun
                );

                DB::table('nota_dinas')->insert([
                    'nomor_nota' => $nomor,
                    'perihal' => 'Nota dinas untuk ' . $skpd->nama_skpd,
                    'anggaran' => 0,
                    'tanggal_pengajuan' => $tanggal,
                    'sub_kegiatan_id' => null,
                    'skpd_id' => $skpd->id,
                    'jenis' => $faker->randomElement([
                        'Perda', 'Perbup', 'SK', 'Rekomendasi', 'Surat',
                        'Telaah', 'Edaran', 'Instruksi'
                    ]),
                    'user_id' => $faker->randomElement($userIds),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
