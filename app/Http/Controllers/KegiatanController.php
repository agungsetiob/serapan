<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Kegiatan;
use App\Models\Skpd;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function store(Request $request, SKPD $skpd)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|digits:4',
        ]);
        if($skpd->status){
            $skpd->kegiatans()->create($validated);
        }else{
            return back()->with('error', 'SKPD nonaktif tidak dapat ditambah kegiatan');
        }
        

        return back()->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kegiatan->update([
            'nama' => $request->nama,
        ]);

        return back()->with('success', 'Nama kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $tahun = $kegiatan->tahun_anggaran;
        $paguKegiatan = $kegiatan->pagu;
    
        $kabupaten = Kabupaten::where('tahun_anggaran', $tahun)->first();
    
        if ($kabupaten) {
            $kabupaten->pagu = max(0, $kabupaten->pagu - $paguKegiatan);
            $kabupaten->save();
        }
    
        $kegiatan->delete();
    
        return back()->with('success', 'Kegiatan dan sub kegiatan berhasil dihapus.');
    }

}
