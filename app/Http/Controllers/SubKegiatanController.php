<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class SubKegiatanController extends Controller
{
    public function store(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pagu' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4',
        ]);

        $kegiatan->subKegiatans()->create($validated);

        return back()->with('success', 'Sub Kegiatan berhasil ditambahkan');
    }

}
