<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaGutulsController extends Controller
{
    public function notaGuTuLsBySkpd(Skpd $skpd, Request $request)
    {
        $search = $request->input('search');
        $tahun = $request->input('tahun') ?? date('Y');

        // Query untuk nota GU/TU/LS
        $notaDinas = NotaDinas::whereIn('jenis', ['GU', 'TU', 'LS'])
            ->whereHas('dikaitkanOleh.subKegiatan.kegiatan', function ($query) use ($skpd) {
                $query->where('skpd_id', $skpd->id);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_nota', 'like', "%$search%")
                    ->orWhere('perihal', 'like', "%$search%");
                });
            })
            ->with(['dikaitkanOleh.subKegiatan.kegiatan', 'dikaitkanOleh'])
            ->paginate(10)
            ->withQueryString();

        // Query untuk parent notes (digunakan modal create dan edit untuk pilihan)
        $parentNotes = NotaDinas::whereIn('jenis', ['Pelaksanaan', 'TU', 'LS'])
            ->whereHas('subKegiatan.kegiatan', fn($q) => $q->where('skpd_id', $skpd->id))
            ->whereYear('tanggal_pengajuan', $tahun)
            ->with('terkait')
            ->get()
            ->map(function ($nota) {
                $nota->total_terkait = $nota->terkait->sum('anggaran');
                $nota->sisa_anggaran = $nota->anggaran - $nota->total_terkait;
                return $nota;
            });

        $notaDinas->getCollection()->transform(function ($nota) {
            $nota->parents = $nota->dikaitkanOleh;
            unset($nota->dikaitkanOleh);
            return $nota;
        });

        //dd($notaDinas->getCollection());
        return inertia('NotaDinas/GuTuLsBySkpd', [
            'skpd' => $skpd,
            'notaDinas' => $notaDinas,
            'parentNotes' => $parentNotes,
            'tahun' => $tahun,
            'search' => $search,
        ]);
    }
    public function storeGuTuLs(Request $request)
    {
        $request->validate([
            'nomor_nota' => 'required|string',
            'perihal' => 'required|string',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'jenis' => 'in:GU,TU,LS',
            'parent_ids' => 'required|array',
            'parent_ids.*' => 'exists:nota_dinas,id',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf',
        ],
        [
            'lampirans.*.max' => 'Setiap file lampiran maksimal 3MB.',
            'lampirans.*.mimes' => 'Setiap file lampiran harus berupa file PDF.',
            'lampirans.*.file' => 'Setiap lampiran harus berupa file yang valid.',
            'parent_ids.required' => 'Nota induk wajib dipilih.',
        ]);

        $anggaran = $request->input('anggaran');
        $parentIds = $request->input('parent_ids');

        $parentNotas = NotaDinas::with('terkait')->whereIn('id', $parentIds)->get();

        $totalSisaAnggaran = 0;
        foreach ($parentNotas as $parent) {
            $terpakai = $parent->terkait->sum('pivot.anggaran');
            $sisa     = $parent->anggaran - $terpakai;
            $totalSisaAnggaran += max($sisa, 0);
        }

        if ($anggaran > $totalSisaAnggaran) {
            return back()->withErrors([
                'anggaran' => "Total anggaran melebihi sisa anggaran. Maksimum: Rp" . number_format($totalSisaAnggaran, 0, ',', '.')
            ]);
        }

        DB::beginTransaction();

        try {
            $notaDina = NotaDinas::create([
                'nomor_nota' => $request->nomor_nota,
                'perihal' => $request->perihal,
                'anggaran' => $anggaran,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jenis' => $request->jenis,
            ]);

            if ($request->hasFile('lampirans')) {
                $attachments = [];
                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('nota_lampirans', 'public');
                    $attachments[] = [
                        'nota_dinas_id' => $notaDina->id,
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ];
                }
                $notaDina->lampirans()->createMany($attachments);
            }

            $remaining = $anggaran;
            $pivotData = [];

            foreach ($parentNotas as $parent) {
                $sisa = $parent->anggaran - $parent->terkait->sum('pivot.anggaran');
                if ($sisa <= 0) continue;

                $pakai = min($remaining, $sisa);
                $pivotData[$parent->id] = ['anggaran' => $pakai];
                $remaining -= $pakai;
                if ($remaining <= 0) break;
            }

            if ($remaining > 0) {
                DB::rollBack();
                return back()->withErrors(['anggaran' => "Tidak cukup sisa anggaran di parent untuk alokasi."]);
            }

            $notaDina->dikaitkanOleh()->attach($pivotData);

            DB::commit();

            $firstParent = $parentNotas->first();
            $skpdId = optional($firstParent->subKegiatan?->kegiatan)->skpd_id ?? $firstParent->skpd_id;

            if (!$skpdId) {
                return back()->withErrors(['parent_ids' => 'Tidak dapat menentukan SKPD dari nota parent yang dipilih.']);
            }

            return redirect()->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
                ->with('success', 'Nota berhasil ditambahkan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan nota: ' . $e->getMessage()]);
        }
    }
    public function updateGuTuLs(Request $request, NotaDinas $notaDina)
    {
        $request->validate([
            'nomor_nota' => 'required|string|unique:nota_dinas,nomor_nota,' . $notaDina->id,
            'perihal' => 'required|string',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'jenis' => 'in:GU,TU,LS',
            'parent_ids' => 'required|array',
            'parent_ids.*' => 'exists:nota_dinas,id',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf',
        ]);

        $anggaran = $request->input('anggaran');
        $parentIds = $request->input('parent_ids');

        $parentNotas = NotaDinas::with('terkait')->whereIn('id', $parentIds)->get();

        $totalSisaAnggaran = 0;
        foreach ($parentNotas as $parent) {
            $terpakai = $parent->terkait->filter(fn($child) => $child->id !== $notaDina->id)->sum(fn ($child) => $child->pivot->anggaran ?? 0);
            $sisa     = $parent->anggaran - $terpakai;
            $totalSisaAnggaran += max($sisa, 0);
        }

        if ($anggaran > $totalSisaAnggaran) {
            return back()->withErrors([
                'anggaran' => "Total anggaran melebihi sisa anggaran. Maksimum: Rp" . number_format($totalSisaAnggaran, 0, ',', '.')
            ]);
        }

        DB::beginTransaction();

        try {
            $notaDina->update([
                'nomor_nota' => $request->nomor_nota,
                'perihal' => $request->perihal,
                'anggaran' => $anggaran,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jenis' => $request->jenis,
            ]);

            if ($request->hasFile('lampirans')) {
                $notaDina->lampirans()->delete(); // Hapus lampiran lama
                $attachments = [];
                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('nota_lampirans', 'public');
                    $attachments[] = [
                        'nota_dinas_id' => $notaDina->id,
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ];
                }
                $notaDina->lampirans()->createMany($attachments);
            }

            // Hitung ulang alokasi pivot
            $remaining = $anggaran;
            $pivotData = [];

            foreach ($parentNotas as $parent) {
                $terpakai = $parent->terkait->filter(fn($child) => $child->id !== $notaDina->id)->sum(fn ($child) => $child->pivot->anggaran ?? 0);
                $sisa = $parent->anggaran - $terpakai;

                if ($sisa <= 0) continue;

                $pakai = min($remaining, $sisa);
                $pivotData[$parent->id] = ['anggaran' => $pakai];
                $remaining -= $pakai;

                if ($remaining <= 0) break;
            }

            if ($remaining > 0) {
                DB::rollBack();
                return back()->withErrors(['anggaran' => "Tidak cukup sisa anggaran di parent untuk alokasi."]);
            }

            // Sinkronisasi ulang relasi parent
            $notaDina->dikaitkanOleh()->sync($pivotData);

            DB::commit();

            $firstParent = $parentNotas->first();
            $skpdId = optional($firstParent->subKegiatan?->kegiatan)->skpd_id ?? $firstParent->skpd_id;

            return redirect()->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
                ->with('success', 'Nota berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui nota: ' . $e->getMessage()]);
        }
    }

    // public function storeGuTuLs(Request $request)
    // {
    //     $request->validate([
    //         'nomor_nota' => 'required|string',
    //         'perihal' => 'required|string',
    //         'anggaran' => 'required|numeric|min:0',
    //         'tanggal_pengajuan' => 'required|date',
    //         'jenis' => 'in:GU,TU,LS',
    //         'parent_ids' => 'required|array',
    //         'parent_ids.*' => 'exists:nota_dinas,id',
    //         'lampirans.*' => 'nullable|file|max:3072|mimes:pdf',
    //     ],
    //     [
    //         'lampirans.*.max' => 'Setiap file lampiran maksimal 3MB.',
    //         'lampirans.*.mimes' => 'Setiap file lampiran harus berupa file PDF.',
    //         'lampirans.*.file' => 'Setiap lampiran harus berupa file yang valid.',
    //         'parent_ids.required' => 'Nota induk wajib dipilih.',
    //     ]);

    //     $anggaran = $request->input('anggaran');
    //     $parentIds = $request->input('parent_ids');

    //     // Ambil parent nota dan preload relasi terkait
    //     $parentNotas = NotaDinas::with('terkait')->whereIn('id', $parentIds)->get();

    //     $totalSisaAnggaran = 0;
    //     foreach ($parentNotas as $parent) {
    //         $terpakai = $parent->terkait->sum('pivot.anggaran');
    //         $sisa     = $parent->anggaran - $terpakai;
    //         $totalSisaAnggaran += max($sisa, 0);
    //     }

    //     if ($anggaran > $totalSisaAnggaran) {
    //         return back()->withErrors([
    //             'anggaran' => "Total anggaran melebihi sisa anggaran. Maksimum: Rp" . number_format($totalSisaAnggaran, 0, ',', '.')
    //         ]);
    //     }

    //     // Simpan nota GU/TU/LS baru
    //     $notaDina = NotaDinas::create([
    //         'nomor_nota' => $request->nomor_nota,
    //         'perihal' => $request->perihal,
    //         'anggaran' => $anggaran,
    //         'tanggal_pengajuan' => $request->tanggal_pengajuan,
    //         'jenis' => $request->jenis,
    //     ]);

    //     // Upload lampiran jika ada
    //     if ($request->hasFile('lampirans')) {
    //         $attachments = [];
    //         foreach ($request->file('lampirans') as $file) {
    //             $path = $file->store('nota_lampirans', 'public');
    //             $attachments[] = [
    //                 'nota_dinas_id' => $notaDina->id,
    //                 'nama_file' => $file->getClientOriginalName(),
    //                 'path' => $path,
    //             ];
    //         }
    //         $notaDina->lampirans()->createMany($attachments);
    //     }

    //     // Alokasikan anggaran secara berurutan ke parent
    //     $remaining = $anggaran;
    //     $pivotData = [];

    //     foreach ($parentNotas as $parent) {
    //         $sisa = $parent->anggaran - $parent->terkait->sum('pivot.anggaran');
    //         if ($sisa <= 0) continue;

    //         $pakai = min($remaining, $sisa);
    //         $pivotData[$parent->id] = ['anggaran' => $pakai];
    //         $remaining -= $pakai;
    //         if ($remaining <= 0) break;
    //     }

    //     if ($remaining > 0) {
    //         return back()->withErrors([
    //             'anggaran' => "Tidak cukup sisa anggaran di parent untuk alokasi."
    //         ]);
    //     }

    //     // Simpan relasi ke parent beserta alokasi anggaran di pivot
    //     $notaDina->dikaitkanOleh()->attach($pivotData);

    //     // Tentukan SKPD dari parent pertama
    //     $firstParent = $parentNotas->first();
    //     $skpdId = optional($firstParent->subKegiatan?->kegiatan)->skpd_id ?? $firstParent->skpd_id;

    //     if (!$skpdId) {
    //         return back()->withErrors(['parent_ids' => 'Tidak dapat menentukan SKPD dari nota parent yang dipilih.']);
    //     }

    //     return redirect()->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
    //         ->with('success', 'Nota berhasil ditambahkan.');
    // }
}
