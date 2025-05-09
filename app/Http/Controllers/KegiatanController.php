<?php

namespace App\Http\Controllers;

use App\Models\Skpd;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function store(Request $request, SKPD $skpd)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pagu' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4',
        ]);

        $skpd->kegiatans()->create($validated);

        return back()->with('success', 'Kegiatan berhasil ditambahkan');
    }

}
