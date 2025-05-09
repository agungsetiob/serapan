<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\NotaLampiran;
use App\Models\SubKegiatan;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotaDinasController extends Controller
{
    public function index()
    {
        $query = NotaDinas::with('subKegiatan.kegiatan.skpd')
            ->orderByDesc('created_at');

        if (request()->filled('search')) {
            $searchTerm = request('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nomor_nota', 'like', "%{$searchTerm}%")
                ->orWhere('perihal', 'like', "%{$searchTerm}%");
            });
        }
        
        $notas = $query->paginate(10);
        $subKegiatans = SubKegiatan::with('kegiatan.skpd')->get();

        return inertia('NotaDinas/Index', [
            'notas' => $notas,
            'subKegiatans' => $subKegiatans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
            'lampirans.*' => 'nullable|file|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $nota = NotaDinas::create([
                'nomor_nota' => $validated['nomor_nota'],
                'perihal' => $validated['perihal'],
                'anggaran' => $validated['anggaran'],
                'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                'sub_kegiatan_id' => $validated['sub_kegiatan_id'],
            ]);

            // Simpan lampiran
            if ($request->hasFile('lampirans')) {
                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('lampirans');
                    NotaLampiran::create([
                        'nota_dinas_id' => $nota->id,
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }

            // Update serapan sub_kegiatan
            $sub = SubKegiatan::find($nota->sub_kegiatan_id);
            $total = NotaDinas::where('sub_kegiatan_id', $sub->id)->sum('anggaran');
            $sub->update([
                'total_serapan' => $total,
                'presentase_serapan' => $sub->pagu > 0 ? ($total / $sub->pagu) * 100 : 0,
            ]);

            // Update serapan kegiatan
            $kegiatan = $sub->kegiatan;
            $subTotals = $kegiatan->subKegiatans()->sum('total_serapan');
            $kegiatan->update([
                'total_serapan' => $subTotals,
                'presentase_serapan' => $kegiatan->pagu > 0 ? ($subTotals / $kegiatan->pagu) * 100 : 0,
            ]);

            // Update serapan kabupaten
            $kabupaten = $kegiatan->skpd->kabupatens()
                ->where('tahun_anggaran', $kegiatan->tahun_anggaran)
                ->first();

            if ($kabupaten) {
                $skpdIds = $kabupaten->skpds->pluck('id');
                $kegiatanIds = Kegiatan::whereIn('skpd_id', $skpdIds)
                    ->where('tahun_anggaran', $kegiatan->tahun_anggaran)
                    ->pluck('id');

                $subTotal = SubKegiatan::whereIn('kegiatan_id', $kegiatanIds)->sum('total_serapan');

                $kabupaten->update([
                    'total_serapan' => $subTotal,
                    'presentase_serapan' => $kabupaten->pagu > 0 ? ($subTotal / $kabupaten->pagu) * 100 : 0,
                ]);
            }
        });

        return back()->with('success', 'Nota dinas berhasil ditambahkan.');
    }

    public function getLampiran($id)
    {
        $notaDinas = NotaDinas::with('lampirans')->findOrFail($id);

        $lampirans = $notaDinas->lampirans
            ->sortByDesc('created_at')
            ->map(function ($lampiran) {
                return [
                    'name' => $lampiran->nama_file,
                    'url' => asset('storage/' . $lampiran->path),
                    'created_at' => $lampiran->created_at,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => $lampirans
        ]);
    }
    
}
