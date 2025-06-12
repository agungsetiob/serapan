<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Skpd;
use Illuminate\Http\Request;

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
            ->with('dikaitkanOleh.subKegiatan.kegiatan')
            ->paginate(10)
            ->withQueryString();

        // Query untuk parent notes (digunakan modal create)
        $parentNotes = NotaDinas::whereIn('jenis', ['Pelaksanaan', 'Perbup', 'Lain-lain'])
            ->whereHas('subKegiatan.kegiatan', fn($q) => $q->where('skpd_id', $skpd->id))
            ->whereYear('tanggal_pengajuan', $tahun)
            ->with('terkait')
            ->get()
            ->map(function ($nota) {
                $nota->total_terkait = $nota->terkait->sum('anggaran');
                $nota->sisa_anggaran = $nota->anggaran - $nota->total_terkait;
                return $nota;
            });

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
        ]);

        $anggaran = $request->input('anggaran');
        $parentIds = $request->input('parent_ids');

        $totalSisaAnggaran = 0;
        $parentNotas = NotaDinas::with('terkait')->whereIn('id', $parentIds)->get();

        foreach ($parentNotas as $parent) {
            $terpakai = $parent->terkait->sum('anggaran');
            $sisa = $parent->anggaran - $terpakai;
            $totalSisaAnggaran += $sisa;
        }

        if ($anggaran > $totalSisaAnggaran) {
            return back()->withErrors([
                'anggaran' => "Total anggaran melebihi sisa anggaran. Maksimum: Rp" . number_format($totalSisaAnggaran, 0, ',', '.')
            ]);
        }

        // Simpan nota GU/TU/LS
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

        // Simpan relasi ke parent
        $notaDina->dikaitkanOleh()->attach($parentIds);

        $firstParent = $parentNotas->first();
        $skpdId = optional($firstParent->subKegiatan->kegiatan)->skpd_id;

        if (!$skpdId) {
            return back()->withErrors(['parent_ids' => 'Tidak dapat menentukan SKPD dari nota parent yang dipilih.']);
        }

        return redirect()->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
            ->with('success', 'Nota berhasil ditambahkan.');
    }
}
