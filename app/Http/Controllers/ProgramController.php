<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Kabupaten;
use App\Models\Skpd;
use App\Models\SkpdTahun;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SKPD  $skpd
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, SKPD $skpd)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required',
        ]);
        $kabupaten = Kabupaten::where('tahun_anggaran', $validated['tahun_anggaran'])->first();
        if (!$kabupaten) {
            return back()
                ->with('error', 'Data kabupaten untuk tahun ini belum tersedia.');
        }

        // Cek apakah SKPD terdaftar untuk tahun anggaran yang diberikan
        $skpdTahunExists = SkpdTahun::where('skpd_id', $skpd->id)
                                    ->where('tahun_anggaran', $validated['tahun_anggaran'])
                                    ->exists();

        if (!$skpdTahunExists) {
            return back()->with('error', $skpd->nama_skpd. ' belum terdaftar untuk tahun anggaran ' . $validated['tahun_anggaran'] . '.');
        }

        if ($skpd->status) {
            $skpd->programs()->create($validated);
        } else {
            return back()->with('error', 'SKPD nonaktif tidak dapat ditambah program.');
        }

        return back()->with('success', 'Program berhasil ditambahkan.');
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|integer|min:1900|max:2100',
        ]);

        $program->update($validated);

        return back()->with('success', 'Program berhasil diperbarui.');
    }

    /**
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Program $program)
    {
        if ($program->kegiatans()->exists()) {
            return back()->with('error', 'Program tidak dapat dihapus karena memiliki kegiatan terkait.');
        }
        try {
            $program->delete();
            return back()->with('success', 'Program berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus program: ' . $e->getMessage());
        }
        
    }
}
