<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\SkpdTahun;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Helpers\SerapanHelper;

class KabupatenController extends Controller
{
    public function index()
    {
        $tahun = date('Y');

        $kabupatens = Kabupaten::with([
            'skpds.programs' => function ($query) use ($tahun) {
                $query->where('tahun_anggaran', $tahun)
                    ->with([
                        'kegiatans' => function ($q) use ($tahun) {
                            $q->where('tahun_anggaran', $tahun)
                                ->with([
                                    'subKegiatans' => function ($sq) use ($tahun) {
                                        $sq->where('tahun_anggaran', $tahun)
                                            ->with('notaDinas.terkait');
                                    }
                                ]);
                        }
                    ]);
            }
        ])
            ->paginate(10)
            ->through(function ($kab) {
                $totalPagu = 0;
                $totalSerapan = 0;

                foreach ($kab->skpds as $skpd) {
                    foreach ($skpd->programs as $program) {
                        SerapanHelper::hitungProgram($program);
                        $totalPagu += $program->pagu;
                        $totalSerapan += $program->total_serapan;
                    }
                }

                $kab->pagu_dinamis = $totalPagu;
                $kab->total_serapan_dinamis = $totalSerapan;
                $kab->presentase_serapan_dinamis = $totalPagu > 0
                    ? round(($totalSerapan / $totalPagu) * 100, 2)
                    : 0;

                return $kab;
            });

        return Inertia::render('Kabupaten/Index', [
            'kabupatens' => $kabupatens,
        ]);
    }
    // public function index()
    // {
    //     $kabupatens = Kabupaten::paginate(10);

    //     return Inertia::render('Kabupaten/Index', [
    //         'kabupatens' => $kabupatens,
    //     ]);
    // }

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

}
