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
        $query = NotaDinas::with(['subKegiatan' => ['kegiatan' => ['skpd']]])
            ->orderByDesc('created_at');

        if (request()->filled('search')) {
            $searchTerm = request('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nomor_nota', 'like', "%{$searchTerm}%")
                    ->orWhere('perihal', 'like', "%{$searchTerm}%")
                    ->orWhereHas('subKegiatan.kegiatan.skpd', function ($skpdQuery) use ($searchTerm) {
                        $skpdQuery->where('nama_skpd', 'like', "%{$searchTerm}%");
                    });
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
        $subKegiatan = SubKegiatan::find($request->sub_kegiatan_id);
        
        if (!$subKegiatan) {
            return redirect()->back()->with('error', 'Sub kegiatan tidak ditemukan')->withInput();
        }

        $totalSerapan = $subKegiatan->notaDinas()->sum('anggaran');
        $sisaPagu = $subKegiatan->pagu - $totalSerapan;

        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'anggaran' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($sisaPagu) {
                    if ($value > $sisaPagu) {
                        $fail('Anggaran melebihi sisa pagu sub kegiatan. Sisa pagu: Rp ' . number_format($sisaPagu, 2, ',', '.'));
                    }
                },
            ],
            'tanggal_pengajuan' => 'required|date',
            'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                $nota = NotaDinas::create([
                    'nomor_nota' => $validated['nomor_nota'],
                    'perihal' => $validated['perihal'],
                    'anggaran' => $validated['anggaran'],
                    'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                    'sub_kegiatan_id' => $validated['sub_kegiatan_id'],
                ]);

                if ($request->hasFile('lampirans')) {
                    $attachments = [];
                    foreach ($request->file('lampirans') as $file) {
                        $path = $file->store('nota_lampirans', 'public');
                        $attachments[] = [
                            'nota_dinas_id' => $nota->id,
                            'nama_file' => $file->getClientOriginalName(),
                            'path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    NotaLampiran::insert($attachments);
                }

                $this->updateBudgetAbsorption($nota);

                return redirect()->back()->with('success', 'Nota dinas berhasil ditambahkan.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan nota dinas: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function update(Request $request, NotaDinas $nota)
    {
        $subKegiatan = $nota->subKegiatan;

        if (!$subKegiatan) {
            return redirect()->back()->with('error', 'Sub kegiatan tidak ditemukan')->withInput();
        }

        $totalSerapanLama = $subKegiatan->notaDinas()->sum('anggaran');
        $sisaPagu = ($subKegiatan->pagu - $totalSerapanLama) + $nota->anggaran;

        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'anggaran' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($sisaPagu) {
                    if ($value > $sisaPagu) {
                        $fail('Anggaran melebihi sisa pagu sub kegiatan. Sisa pagu: Rp ' . number_format($sisaPagu, 2, ',', '.'));
                    }
                },
            ],
            'tanggal_pengajuan' => 'required|date',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request, $nota) {
                $nota->update([
                    'nomor_nota' => $validated['nomor_nota'],
                    'perihal' => $validated['perihal'],
                    'anggaran' => $validated['anggaran'],
                    'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                ]);

                if ($request->hasFile('lampirans')) {
                    foreach ($nota->lampirans as $lampiran) {
                        Storage::disk('public')->delete($lampiran->path); // Delete old files
                        $lampiran->delete();
                    }

                    $attachments = [];
                    foreach ($request->file('lampirans') as $file) {
                        $path = $file->store('nota_lampirans', 'public');
                        $attachments[] = [
                            'nota_dinas_id' => $nota->id,
                            'nama_file' => $file->getClientOriginalName(),
                            'path' => $path,
                        ];
                    }
                    $nota->lampirans()->createMany($attachments);
                }

                $this->updateBudgetAbsorption($nota);

                return redirect()->back()->with('success', 'Nota dinas berhasil diperbarui.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui nota dinas: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function destroy(NotaDinas $nota)
    {
        try {
            return DB::transaction(function () use ($nota) {
                // Delete lampiran files first
                foreach ($nota->lampirans as $lampiran) {
                    Storage::disk('public')->delete($lampiran->path); // Delete file
                    $lampiran->delete(); // Remove DB record
                }

                // Delete nota
                $nota->delete();

                // Update budget absorption
                $this->updateBudgetAbsorption($nota);

                return redirect()->back()->with('success', 'Nota dinas berhasil dihapus.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus nota dinas: ' . $e->getMessage());
        }
    }  
    protected function updateBudgetAbsorption(NotaDinas $nota)
    {
        // Hitung Sub Kegiatan
        $subKegiatan = $nota->subKegiatan()->with(['kegiatan.skpd.kabupatens'])->first();
        
        $subKegiatan->update([
            'total_serapan' => $subKegiatan->notaDinas()->sum('anggaran'),
            'presentase_serapan' => $subKegiatan->pagu > 0 
                ? ($subKegiatan->notaDinas()->sum('anggaran') / $subKegiatan->pagu) * 100 
                : 0,
        ]);
    
        // Hitung Kegiatan
        $kegiatan = $subKegiatan->kegiatan;
        $kegiatan->update([
            'total_serapan' => $kegiatan->subKegiatans()->sum('total_serapan'),
            'presentase_serapan' => $kegiatan->pagu > 0 
                ? ($kegiatan->subKegiatans()->sum('total_serapan') / $kegiatan->pagu) * 100 
                : 0,
        ]);
    
        $skpd = $kegiatan->skpd;
        
        // Get Tahun Anggaran
        $kabupaten = $skpd->kabupatens()
            ->wherePivot('tahun_anggaran', $kegiatan->tahun_anggaran)
            ->first();
    
        if ($kabupaten) {
            // Hitung serapan skpd tahun anggaran berjalan
            $totalSerapan = SubKegiatan::whereHas('kegiatan', function ($query) use ($kabupaten, $kegiatan) {
                    $query->whereHas('skpd', function ($q) use ($kabupaten) {
                        $q->whereHas('kabupatens', function ($k) use ($kabupaten) {
                            $k->where('kabupatens.id', $kabupaten->id);
                        });
                    })
                    ->where('tahun_anggaran', $kegiatan->tahun_anggaran);
                })
                ->sum('total_serapan');
    
            // Update serapan kabupaten
            $kabupaten->update([
                'total_serapan' => $totalSerapan,
                'presentase_serapan' => $kabupaten->pagu > 0 
                    ? ($totalSerapan / $kabupaten->pagu) * 100 
                    : 0,
            ]);
        }
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
