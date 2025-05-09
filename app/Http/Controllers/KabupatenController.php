<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\SkpdTahun;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'tahun_anggaran' => 'required|integer|min:2000',
            'pagu' => 'required|numeric|min:0',
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
            'pagu' => 'required|numeric|min:0',
        ]);

        $kabupaten->update($validated);

        return back()->with('success', 'Kabupaten berhasil diperbarui.');
    }
}
