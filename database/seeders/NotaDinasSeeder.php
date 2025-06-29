<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class NotaDinasSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Format bulan Romawi
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        // Ambil semua sub_kegiatan beserta informasi kegiatan dan skpd terkait
        $subKegiatans = DB::table('sub_kegiatans')
            ->join('kegiatans', 'sub_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('skpds', 'kegiatans.skpd_id', '=', 'skpds.id')
            ->select(
                'sub_kegiatans.id as sub_kegiatan_id',
                'kegiatans.id as kegiatan_id',
                'skpds.id as skpd_id'
            )
            ->get();

        foreach ($subKegiatans as $subKegiatan) {
            for ($i = 1; $i <= 2; $i++) {
                $tanggal = Carbon::createFromDate(now()->year, 1, 1)->addDays(rand(0, now()->dayOfYear - 1));
                $bulan = (int)$tanggal->format('n');
                $tahun = $tanggal->format('Y');
                $romawi = $bulanRomawi[$bulan];

                $nomorNota = sprintf(
                    'B/000.1.2.3/%04d/RSUD-ADMKEU.1/%s/%s/SETDA/TANBU',
                    rand(1000, 9999),
                    $romawi,
                    $tahun
                );

                DB::table('nota_dinas')->insert([
                    'nomor_nota' => $nomorNota,
                    'perihal' => "Perihal Nota Dinas $i untuk Sub Kegiatan {$subKegiatan->sub_kegiatan_id}",
                    'anggaran' => 0,
                    'tanggal_pengajuan' => $tanggal,
                    'sub_kegiatan_id' => $subKegiatan->sub_kegiatan_id,
                    'skpd_id' => $subKegiatan->skpd_id,
                    'jenis' => $faker->randomElement(['Pelaksanaan', 'TU', 'LS']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
