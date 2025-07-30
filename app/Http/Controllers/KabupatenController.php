<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\SkpdTahun;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Support\Facades\DB;

class KabupatenController extends Controller
{
    public function index()
    {
        $kabupatens = Kabupaten::paginate(10);

        return Inertia::render('Kabupaten/Index', [
            'kabupatens' => $kabupatens,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|integer|min:2024|unique:kabupatens',
        ]);

        // Buat pagu kabupaten baru
        $kabupaten = Kabupaten::create($validated);

        // Ambil semua SKPD aktif
        $skpds = Skpd::where('status', true)->get();

        // Mapping semua SKPD ke tahun anggaran baru
        foreach ($skpds as $skpd) {
            SkpdTahun::firstOrCreate([
                'skpd_id' => $skpd->id,
                'kabupaten_id' => $kabupaten->id,
                'tahun_anggaran' => $kabupaten->tahun_anggaran,
            ]);
        }

        return back()->with('success', 'Kabupaten dan mapping SKPD berhasil ditambahkan.');
    }

    public function update(Request $request, Kabupaten $kabupaten)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|integer|min:2000',
        ]);

        $kabupaten->update($validated);

        // Cek SKPD aktif yang belum dimapping untuk tahun_anggaran tertentu
        $mappedSkpdIds = SkpdTahun::where('kabupaten_id', $kabupaten->id)
            ->where('tahun_anggaran', $kabupaten->tahun_anggaran)
            ->pluck('skpd_id')
            ->toArray();

        $unmappedSkpds = Skpd::where('status', true)
            ->whereNotIn('id', $mappedSkpdIds)
            ->get();

        // Mapping SKPD yang belum dimapping
        foreach ($unmappedSkpds as $skpd) {
            SkpdTahun::create([
                'skpd_id' => $skpd->id,
                'kabupaten_id' => $kabupaten->id,
                'tahun_anggaran' => $kabupaten->tahun_anggaran,
            ]);
        }

        return back()->with('success', 'Kabupaten berhasil diperbarui dan SKPD yang belum dimapping telah ditambahkan.');
    }
    public function copyFromPrevious(Kabupaten $kabupaten)
    {
        $tahunLalu = $kabupaten->tahun_anggaran - 1;

        // Cari kabupaten sumber
        $kabupatenSumber = Kabupaten::where('nama', $kabupaten->nama)
            ->where('tahun_anggaran', $tahunLalu)
            ->first();

        if (!$kabupatenSumber) {
            return back()->with('error', 'Data tahun sebelumnya tidak ditemukan.');
        }

        $sudahAda = Program::where('tahun_anggaran', $kabupaten->tahun_anggaran)
            ->whereIn('skpd_id', SkpdTahun::where('kabupaten_id', $kabupaten->id)->pluck('skpd_id'))
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Copy sudah pernah dilakukan untuk tahun ini. Proses dibatalkan.');
        }

        try {
            DB::transaction(function () use ($kabupaten, $kabupatenSumber) {

                // Ambil mapping SKPD ID lama -> baru (berdasarkan nama SKPD)
                $skpdMapping = [];
                $skpdsBaru = SkpdTahun::where('kabupaten_id', $kabupaten->id)->get();
                foreach ($skpdsBaru as $skpdBaru) {
                    $skpdLama = SkpdTahun::where('kabupaten_id', $kabupatenSumber->id)
                        ->where('skpd_id', $skpdBaru->skpd_id)
                        ->first();
                    if ($skpdLama) {
                        $skpdMapping[$skpdLama->skpd_id] = $skpdBaru->skpd_id;
                    }
                }

                // 1. Copy Program
                $programMapping = [];
                $programSumber = Program::where('tahun_anggaran', $kabupatenSumber->tahun_anggaran)
                    ->whereIn('skpd_id', array_keys($skpdMapping))
                    ->get();

                foreach ($programSumber as $program) {
                    $new = $program->replicate();
                    $new->skpd_id = $skpdMapping[$program->skpd_id];
                    $new->tahun_anggaran = $kabupaten->tahun_anggaran;
                    $new->save();
                    $programMapping[$program->id] = $new->id;
                }

                // 2. Copy Kegiatan
                $kegiatanMapping = [];
                $kegiatanSumber = Kegiatan::where('tahun_anggaran', $kabupatenSumber->tahun_anggaran)
                    ->whereIn('program_id', array_keys($programMapping))
                    ->get();

                foreach ($kegiatanSumber as $kegiatan) {
                    $new = $kegiatan->replicate();
                    $new->program_id = $programMapping[$kegiatan->program_id];
                    $new->skpd_id = $skpdMapping[$kegiatan->skpd_id];
                    $new->tahun_anggaran = $kabupaten->tahun_anggaran;
                    $new->save();
                    $kegiatanMapping[$kegiatan->id] = $new->id;
                }

                // 3. Copy Sub Kegiatan
                $subSumber = SubKegiatan::where('tahun_anggaran', $kabupatenSumber->tahun_anggaran)
                    ->whereIn('kegiatan_id', array_keys($kegiatanMapping))
                    ->get();

                foreach ($subSumber as $sub) {
                    $new = $sub->replicate();
                    $new->kegiatan_id = $kegiatanMapping[$sub->kegiatan_id];
                    $new->tahun_anggaran = $kabupaten->tahun_anggaran;
                    $new->save();
                }
            });

            return back()->with('success', 'Data berhasil dicopy dari tahun sebelumnya.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencopy data: ' . $e->getMessage());
        }
    }
}
