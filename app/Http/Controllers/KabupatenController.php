<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
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

        Kabupaten::create($validated);

        return back()->with('success', 'Kabupaten berhasil ditambahkan.');
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
