<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubKegiatanController extends Controller
{
    public function store(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pagu' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4',
            'kode_rekening' => 'required|string|max:50',
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
        $subKegiatan = $kegiatan->subKegiatans()
            ->with(['notaDinas', 'kegiatan.skpd.kabupatens'])
            ->where('id', $subKegiatanId)
            ->firstOrFail();

        // Validasi: Pagu tidak boleh kurang dari total anggaran semua nota dinas milik SubKegiatan
        $totalAnggaranNota = $subKegiatan->notaDinas->sum('anggaran');

        if ($request->pagu < $totalAnggaranNota) {
            return back()->withErrors([
                'pagu' => 'Pagu tidak boleh lebih kecil dari total anggaran semua nota dinas aktif milik sub kegiatan ini. Total saat ini: Rp ' .
                    number_format($totalAnggaranNota, 2, ',', '.')
            ])->withInput();
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pagu' => 'required|numeric',
            'tahun_anggaran' => 'required|digits:4',
            'kode_rekening' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $selisihPagu = $validated['pagu'] - $subKegiatan->pagu;
            $oldTotalSerapan = $subKegiatan->total_serapan;

            // Recalculate total serapan subKegiatan berdasarkan nota dinas terkait
            $newTotalSerapan = $subKegiatan->notaDinas->flatMap(function ($nota) {
                return $nota->terkait;
            })->sum('anggaran');

            $subKegiatan->update([
                'nama' => $validated['nama'],
                'pagu' => $validated['pagu'],
                'kode_rekening' => $validated['kode_rekening'],
                'tahun_anggaran' => $validated['tahun_anggaran'],
                'total_serapan' => $newTotalSerapan,
                'presentase_serapan' => $validated['pagu'] > 0
                    ? ($newTotalSerapan / $validated['pagu']) * 100
                    : 0
            ]);

            // Hitung total serapan baru berdasarkan nota terkait
            $newSerapanKegiatan = $kegiatan->subKegiatans->flatMap(function ($sub) {
                return $sub->notaDinas->flatMap->terkait;
            })->sum('anggaran');

            $kegiatan->increment('pagu', $selisihPagu);
            $kegiatan->decrement('total_serapan', $oldTotalSerapan);
            $kegiatan->increment('total_serapan', $newSerapanKegiatan);
            $kegiatan->update([
                'presentase_serapan' => $kegiatan->pagu > 0
                    ? ($kegiatan->total_serapan / $kegiatan->pagu) * 100
                    : 0,
            ]);

            // Update Kabupaten
            $kabupaten = $subKegiatan->kegiatan->skpd->kabupatens()
                ->wherePivot('tahun_anggaran', $subKegiatan->tahun_anggaran)
                ->first();

            if ($kabupaten) {
                $kabupaten->increment('pagu', $selisihPagu);

                $totalSerapanKabupaten = SubKegiatan::whereHas('kegiatan', function ($query) use ($kabupaten, $subKegiatan) {
                    $query->whereHas('skpd', function ($q) use ($kabupaten) {
                        $q->whereHas('kabupatens', function ($k) use ($kabupaten) {
                            $k->where('kabupatens.id', $kabupaten->id);
                        });
                    })->where('tahun_anggaran', $subKegiatan->tahun_anggaran);
                })->with(['notaDinas.terkait'])
                    ->get()
                    ->flatMap(function ($sub) {
                        return $sub->notaDinas->flatMap->terkait;
                    })
                    ->sum('anggaran');

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

            foreach ($subKegiatan->notaDinas as $notaDinas) {
                foreach ($notaDinas->lampirans as $lampiran) {
                    Storage::disk('public')->delete($lampiran->path);
                    $lampiran->delete();
                }
                $notaDinas->delete();
            }

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
            $totalSerapanKabupaten = SubKegiatan::whereHas('kegiatan', function ($query) use ($kabupaten, $subKegiatan) {
                $query->whereHas('skpd', function ($q) use ($kabupaten) {
                    $q->whereHas('kabupatens', function ($k) use ($kabupaten) {
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

}
