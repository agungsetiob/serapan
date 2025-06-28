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
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SKPD  $skpd
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, SKPD $skpd)
    {
        // Validasi data input, termasuk program_id
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_anggaran' => 'required|digits:4',
            'program_id' => 'required|exists:programs,id',
        ]);

        // Cek ketersediaan data kabupaten untuk tahun anggaran yang diberikan
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

        // Cek status SKPD
        if ($skpd->status) {
            $skpd->kegiatans()->create([
                'program_id' => $validated['program_id'],
                'nama' => $validated['nama'],
                'tahun_anggaran' => $validated['tahun_anggaran'],
            ]);
        } else {
            return back()->with('error', 'SKPD nonaktif tidak dapat ditambah kegiatan.');
        }

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            // 'program_id' => 'required|exists:programs,id',
        ]);

        $kegiatan->update([
            'nama' => $request->nama,
            // 'program_id' => $request->program_id,
        ]);

        return back()->with('success', 'Nama kegiatan berhasil diperbarui.');
    }

    /**
     *
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Kegiatan $kegiatan)
    {
        DB::beginTransaction();
        try {
            $tahun = $kegiatan->tahun_anggaran;
            
            $kabupaten = Kabupaten::where('tahun_anggaran', $tahun)->first();
            
            // Perbarui pagu dan serapan kabupaten jika ada
            if ($kabupaten) {
                $kabupaten->decrement('pagu', $kegiatan->pagu);
                $kabupaten->decrement('total_serapan', $kegiatan->total_serapan);
                
                $kabupaten->update([
                    'presentase_serapan' => $kabupaten->pagu > 0 
                        ? ($kabupaten->total_serapan / $kabupaten->pagu) * 100 
                        : 0
                ]);
            }
            
            // Hapus subKegiatans, notaDinas, dan lampiran terkait
            foreach ($kegiatan->subKegiatans as $subKegiatan) {
                foreach ($subKegiatan->notaDinas as $notaDinas) {
                    foreach ($notaDinas->lampirans as $lampiran) {
                        Storage::disk('public')->delete($lampiran->path);
                        $lampiran->delete();
                    }
                    $notaDinas->delete();
                }
            }

            // Hapus kegiatan itu sendiri
            $kegiatan->delete(); 

            DB::commit();
            
            return back()->with('success', 'Kegiatan beserta seluruh data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
