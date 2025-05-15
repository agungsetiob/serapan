<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
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

        $kabupaten = \App\Models\Kabupaten::where('tahun_anggaran', $validated['tahun_anggaran'])->first();

        if (!$kabupaten) {
            return back()
                ->with('error', 'Data kabupaten untuk tahun ini belum tersedia.');
        }

        DB::beginTransaction();

        try {
            $kegiatan->subKegiatans()->create($validated);
        
            $kegiatan->increment('pagu', $validated['pagu']);
            $kabupaten->increment('pagu', $validated['pagu']);

            DB::commit();

            return back()->with('success', 'Sub Kegiatan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
    public function update(Request $request, Kegiatan $kegiatan, $subKegiatanId)
    {
        $subKegiatan = $kegiatan->subKegiatans()->with(['notaDinas', 'kegiatan.skpd.kabupatens'])->where('id', $subKegiatanId)->firstOrFail();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pagu' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4',
        ]);

        DB::beginTransaction();
        try {
            $selisihPagu = $validated['pagu'] - $subKegiatan->pagu;
            $oldTotalSerapan = $subKegiatan->total_serapan;

            $subKegiatan->update($validated);

            $kegiatan->increment('pagu', $selisihPagu);
            $kegiatan->decrement('total_serapan', $oldTotalSerapan);
            $kegiatan->increment('total_serapan', $subKegiatan->notaDinas()->sum('anggaran'));
            $kegiatan->update([
                'presentase_serapan' => $kegiatan->pagu > 0 
                    ? ($kegiatan->total_serapan / $kegiatan->pagu) * 100 
                    : 0,
            ]);

            $kabupaten = $subKegiatan->kegiatan->skpd->kabupatens()
                ->wherePivot('tahun_anggaran', $subKegiatan->tahun_anggaran)
                ->first();

            if ($kabupaten) {
                $kabupaten->increment('pagu', $selisihPagu);
                
                // Recalculate total serapan for kabupaten
                $totalSerapanKabupaten = SubKegiatan::whereHas('kegiatan', function($query) use ($kabupaten, $subKegiatan) {
                        $query->whereHas('skpd', function($q) use ($kabupaten) {
                            $q->whereHas('kabupatens', function($k) use ($kabupaten) {
                                $k->where('kabupatens.id', $kabupaten->id);
                            });
                        })
                        ->where('tahun_anggaran', $subKegiatan->tahun_anggaran);
                    })
                    ->sum('total_serapan');

                $kabupaten->update([
                    'total_serapan' => $totalSerapanKabupaten,
                    'presentase_serapan' => $kabupaten->pagu > 0 
                        ? ($totalSerapanKabupaten / $kabupaten->pagu) * 100 
                        : 0,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Sub Kegiatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
        }
    }
    public function destroy(Kegiatan $kegiatan, $subKegiatanId)
    {
        $subKegiatan = $kegiatan->subKegiatans()
            ->with(['notaDinas', 'kegiatan.skpd.kabupatens'])
            ->where('id', $subKegiatanId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $kabupaten = $subKegiatan->kegiatan->skpd->kabupatens()
                ->wherePivot('tahun_anggaran', $subKegiatan->tahun_anggaran)
                ->first();

            if (!$kabupaten) {
                return back()->with('error', 'Data kabupaten untuk tahun ini belum tersedia.');
            }

            $subKegiatanPagu = $subKegiatan->pagu;
            $subKegiatanSerapan = $subKegiatan->total_serapan;

            $subKegiatan->notaDinas()->delete();

            $subKegiatan->delete();

            $kegiatan->decrement('pagu', $subKegiatanPagu);
            $kegiatan->decrement('total_serapan', $subKegiatanSerapan);
            $kegiatan->update([
                'presentase_serapan' => $kegiatan->pagu > 0 
                    ? ($kegiatan->total_serapan / $kegiatan->pagu) * 100 
                    : 0,
            ]);

            $kabupaten->decrement('pagu', $subKegiatanPagu);
            
            // Recalculate total serapan for kabupaten
            $totalSerapanKabupaten = SubKegiatan::whereHas('kegiatan', function($query) use ($kabupaten, $subKegiatan) {
                    $query->whereHas('skpd', function($q) use ($kabupaten) {
                        $q->whereHas('kabupatens', function($k) use ($kabupaten) {
                            $k->where('kabupatens.id', $kabupaten->id);
                        });
                    })
                    ->where('tahun_anggaran', $subKegiatan->tahun_anggaran);
                })
                ->sum('total_serapan');

            $kabupaten->update([
                'total_serapan' => $totalSerapanKabupaten,
                'presentase_serapan' => $kabupaten->pagu > 0 
                    ? ($totalSerapanKabupaten / $kabupaten->pagu) * 100 
                    : 0,
            ]);

            DB::commit();
            return back()->with('success', 'Sub Kegiatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
    // public function update(Request $request, Kegiatan $kegiatan, $subKegiatanId)
    // {
    //     $subKegiatan = $kegiatan->subKegiatans()->where('id', $subKegiatanId)->firstOrFail();

    //     $validated = $request->validate([
    //         'nama' => 'required|string|max:255',
    //         'pagu' => 'required|numeric',
    //         'tahun_anggaran' => 'required|digits:4',
    //     ]);

    //     $validated['kegiatan_id'] = $kegiatan->id;

    //     $kabupaten = \App\Models\Kabupaten::where('tahun_anggaran', $validated['tahun_anggaran'])->first();
    //     if (!$kabupaten) {
    //         return back()->with('error', 'Data kabupaten untuk tahun ini belum tersedia.')->withInput();
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $selisihPagu = $validated['pagu'] - $subKegiatan->pagu;

    //         $subKegiatan->update($validated);

    //         $kegiatan->increment('pagu', $selisihPagu);
    //         $kabupaten->increment('pagu', $selisihPagu);

    //         DB::commit();
    //         return back()->with('success', 'Sub Kegiatan berhasil diperbarui.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
    //     }
    // }
    // public function show($id)
    // {
    //     $subKegiatan = SubKegiatan::with('notaDinas')->findOrFail($id);

    //     return inertia('SubKegiatan/Show', [
    //         'subKegiatan' => $subKegiatan,
    //     ]);
    // }

    // public function destroy(Kegiatan $kegiatan, $subKegiatanId)
    // {
    //     $subKegiatan = $kegiatan->subKegiatans()->where('id', $subKegiatanId)->firstOrFail();
    //     DB::beginTransaction();
    
    //     try {
    //         $kabupaten = \App\Models\Kabupaten::where('tahun_anggaran', $subKegiatan->tahun_anggaran)->first();
    
    //         if (!$kabupaten) {
    //             return back()->with('error', 'Data kabupaten untuk tahun ini belum tersedia.');
    //         }
    
    //         // Kurangi nilai pagu sebelum sub-kegiatan dihapus
    //         $kegiatan->decrement('pagu', $subKegiatan->pagu);
    //         $kabupaten->decrement('pagu', $subKegiatan->pagu);
    
    //         // Hapus sub-kegiatan
    //         $subKegiatan->delete();
    
    //         DB::commit();
    
    //         return back()->with('success', 'Sub Kegiatan berhasil dihapus.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
    //     }
    // }    

}
