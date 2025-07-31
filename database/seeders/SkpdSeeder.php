<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkpdSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $skpds = [
            ['nama_skpd' => 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia', 'status' => true],
            ['nama_skpd' => 'Badan Kesatuan Bangsa dan Politik', 'status' => true],
            ['nama_skpd' => 'Badan Penanggulangan Bencana Daerah', 'status' => true],
            ['nama_skpd' => 'Badan Pendapatan Daerah', 'status' => true],
            ['nama_skpd' => 'Badan Pengelolaan Keuangan dan Aset Daerah', 'status' => true],
            ['nama_skpd' => 'Badan Perencanaan Pembangunan Daerah, Penelitian dan Pengembangan', 'status' => true],
            ['nama_skpd' => 'Dinas Kebudayaan, Kepemudaan, Olahraga dan Pariwisata', 'status' => true],
            ['nama_skpd' => 'Dinas Kependudukan dan Pencatatan Sipil', 'status' => true],
            ['nama_skpd' => 'Dinas Kesehatan', 'status' => true],
            ['nama_skpd' => 'PKM Batulicin', 'status' => true],
            ['nama_skpd' => 'PKM Batulicin 1', 'status' => true],
            ['nama_skpd' => 'PKM Darul Azhar', 'status' => true],
            ['nama_skpd' => 'PKM Giri Mulya', 'status' => true],
            ['nama_skpd' => 'PKM Karang Bintang', 'status' => true],
            ['nama_skpd' => 'PKM Lasung', 'status' => true],
            ['nama_skpd' => 'PKM Mantewe', 'status' => true],
            ['nama_skpd' => 'PKM Pagatan', 'status' => true],
            ['nama_skpd' => 'PKM Simpang Empat', 'status' => true],
            ['nama_skpd' => 'PKM Pulau Tanjung', 'status' => true],
            ['nama_skpd' => 'PKM Satui', 'status' => true],
            ['nama_skpd' => 'PKM Sebamban 1', 'status' => true],
            ['nama_skpd' => 'PKM Sebamban 2', 'status' => true],
            ['nama_skpd' => 'PKM Teluk Kepayang', 'status' => true],
            ['nama_skpd' => 'UPTD Instalasi Farmasi', 'status' => true],
            ['nama_skpd' => 'UPTD Lab Kesehatan Daerah', 'status' => true],
            ['nama_skpd' => 'Dinas Ketahanan Pangan dan Pertanian', 'status' => true],
            ['nama_skpd' => 'Dinas Komunikasi, Informatika, Statistik dan Persandian', 'status' => true],
            ['nama_skpd' => 'Dinas Koperasi, Usaha Mikro, Perdagangan dan Perindustrian', 'status' => true],
            ['nama_skpd' => 'Dinas Lingkungan Hidup', 'status' => true],
            ['nama_skpd' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'status' => true],
            ['nama_skpd' => 'Dinas Pemberdayaan Masyarakat dan Desa', 'status' => true],
            ['nama_skpd' => 'Dinas Pemberdayaan Perempuan, Perlindungan Anak Pengendalian Penduduk dan Keluarga Berencana', 'status' => true],
            ['nama_skpd' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'status' => true],
            ['nama_skpd' => 'Dinas Pendidikan (Kantor)', 'status' => true],
            ['nama_skpd' => 'Dinas Perhubungan', 'status' => true],
            ['nama_skpd' => 'Dinas Perikanan', 'status' => true],
            ['nama_skpd' => 'Dinas Perpustakaan dan Kearsipan', 'status' => true],
            ['nama_skpd' => 'Dinas Perumahan Rakyat Kawasan Permukiman dan Pertanahan', 'status' => true],
            ['nama_skpd' => 'Dinas Sosial', 'status' => true],
            ['nama_skpd' => 'Dinas Tenaga Kerja dan Transmigrasi', 'status' => true],
            ['nama_skpd' => 'Inspektorat Daerah', 'status' => true],
            ['nama_skpd' => 'Kecamatan Angsana', 'status' => true],
            ['nama_skpd' => 'Kecamatan Batulicin', 'status' => true],
            ['nama_skpd' => 'Kecamatan Karang Bintang', 'status' => true],
            ['nama_skpd' => 'Kecamatan Kuranji', 'status' => true],
            ['nama_skpd' => 'Kecamatan Kusan Hilir', 'status' => true],
            ['nama_skpd' => 'Kecamatan Kusan Hulu', 'status' => true],
            ['nama_skpd' => 'Kecamatan Kusan Tengah', 'status' => true],
            ['nama_skpd' => 'Kecamatan Mantewe', 'status' => true],
            ['nama_skpd' => 'Kecamatan Satui', 'status' => true],
            ['nama_skpd' => 'Kecamatan Simpang Empat', 'status' => true],
            ['nama_skpd' => 'Kecamatan Sungai Loban', 'status' => true],
            ['nama_skpd' => 'Kecamatan Teluk Kepayang', 'status' => true],
            ['nama_skpd' => 'RSUD dr.H. Andi Abdurrahman Noor', 'status' => true],
            ['nama_skpd' => 'Satpol PP dan Damkar Satuan Polisi PP dan Damkar', 'status' => true],
            ['nama_skpd' => 'Sekretariat Daerah', 'status' => true],
            ['nama_skpd' => 'Bagian Pemerintahan', 'status' => true],
            ['nama_skpd' => 'Bagian Perencanaan dan Keuangan', 'status' => true],
            ['nama_skpd' => 'Bagian Hukum', 'status' => true],
            ['nama_skpd' => 'Bagian Kesejahteraan Rakyat', 'status' => true],
            ['nama_skpd' => 'Bagian Perekonomian, SDA dan Administrasi Pembangunan', 'status' => true],
            ['nama_skpd' => 'Bagian Pengadaan Barang dan Jasa', 'status' => true],
            ['nama_skpd' => 'Bagian Umum', 'status' => true],
            ['nama_skpd' => 'Bagian Organisasi', 'status' => true],
            ['nama_skpd' => 'Bagian Protokol dan Komunikasi Pimpinan', 'status' => true],
            ['nama_skpd' => 'Sekretariat DPRD', 'status' => true],
            ['nama_skpd' => 'Kelurahan Kota Pagatan', 'status' => true],
            ['nama_skpd' => 'Kelurahan Batulicin', 'status' => true],
            ['nama_skpd' => 'Kelurahan Tungkaran Pangeran', 'status' => true],
            ['nama_skpd' => 'Kelurahan Kampung Baru', 'status' => true],
            ['nama_skpd' => 'Kelurahan Gunung Tinggi', 'status' => true],
        ];

        foreach ($skpds as &$skpd) {
            $skpd['created_at'] = $now;
            $skpd['updated_at'] = $now;
        }

        DB::table('skpds')->insert($skpds);
    }
}
