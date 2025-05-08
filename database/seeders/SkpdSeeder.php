<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkpdSeeder extends Seeder
{
    public function run()
    {
        $skpds = [
            ['nama_skpd' => 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia', 'status' => true],
            ['nama_skpd' => 'Badan Kesatuan Bangsa dan Politik', 'status' => true],
            ['nama_skpd' => 'Badan Perencanaan Pembangunan Daerah Penelitian dan Pengembangan', 'status' => true],
            ['nama_skpd' => 'Dinas Perhubungan', 'status' => true],
            ['nama_skpd' => 'Dinas Kependudukan dan Pencatatan Sipil', 'status' => true],
            ['nama_skpd' => 'Dinas Kesehatan', 'status' => true],
            ['nama_skpd' => 'Dinas Ketahanan Pangan dan Pertanian', 'status' => false],
            ['nama_skpd' => 'Dinas Pemberdayaan Perempuan, Perlindungan Anak Pengendalian Penduduk dan Keluarga Berencana', 'status' => true],
            ['nama_skpd' => 'RSUD Rumah Sehat Amanah Husada', 'status' => true],
            ['nama_skpd' => 'Dinas Ketahanan Pangan dan Pertanian', 'status' => true],
            ['nama_skpd' => 'Dinas Pendidikan', 'status' => true],
            ['nama_skpd' => 'Dinas Pemberdayaan Masyarakat dan Desa', 'status' => true],
            ['nama_skpd' => 'Dinas Perpustakaan dan Kearsipan', 'status' => true],
        ];        

        DB::table('skpds')->insert($skpds);
    }
}