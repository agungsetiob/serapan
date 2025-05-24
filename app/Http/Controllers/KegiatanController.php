<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Kegiatan;
use App\Models\Skpd;
use App\Models\SkpdTahun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    public function store(Request $request, SKPD $skpd)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|digits:4',
        ]);

        $kabupaten = Kabupaten::where('tahun_anggaran', $validated['tahun_anggaran'])->first();
        if (!$kabupaten) {
            return back()
                ->with('error', 'Data kabupaten untuk tahun ini belum tersedia.');
        }

        // Check if the SKPD is mapped to tahun_anggaran
        $skpdTahunExists = SkpdTahun::where('skpd_id', $skpd->id)
                                    ->where('tahun_anggaran', $validated['tahun_anggaran'])
                                    ->exists();

        if (!$skpdTahunExists) {
            return back()->with('error', $skpd->nama_skpd. ' belum terdaftar untuk tahun anggaran ' . $validated['tahun_anggaran'] . '.');
        }

        if ($skpd->status) {
            $skpd->kegiatans()->create($validated);
        } else {
            return back()->with('error', 'SKPD nonaktif tidak dapat ditambah kegiatan.');
        }

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
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
        DB::beginTransaction();
        try {
            $tahun = $kegiatan->tahun_anggaran;
            
            $kabupaten = Kabupaten::where('tahun_anggaran', $tahun)->first();
            
            if ($kabupaten) {
                $kabupaten->decrement('pagu', $kegiatan->pagu);
                $kabupaten->decrement('total_serapan', $kegiatan->total_serapan);
                
                $kabupaten->update([
                    'presentase_serapan' => $kabupaten->pagu > 0 
                        ? ($kabupaten->total_serapan / $kabupaten->pagu) * 100 
                        : 0
                ]);
            }
            foreach ($kegiatan->subKegiatans as $subKegiatan) {
                foreach ($subKegiatan->notaDinas as $notaDinas) {
                    foreach ($notaDinas->lampirans as $lampiran) {
                        Storage::disk('public')->delete($lampiran->path);
                        $lampiran->delete();
                    }
                    $notaDinas->delete();
                }
            }

            $kegiatan->delete(); 

            DB::commit();
            
            return back()->with('success', 'Kegiatan beserta seluruh data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

}
