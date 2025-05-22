<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NotaDinasController extends Controller
{
    public function index(Request $request)
    {
        $query = NotaDinas::with('subKegiatan.kegiatan.skpd')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
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

        $validated = $request->validate($this->getValidationRules($sisaPagu, false));

        try {
            return DB::transaction(function () use ($validated, $request) {
                $notaDina = NotaDinas::create([
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
                            'nota_dinas_id' => $notaDina->id,
                            'nama_file' => $file->getClientOriginalName(),
                            'path' => $path,
                        ];
                    }
                    $notaDina->lampirans()->createMany($attachments);
                }

                $this->updateBudgetAbsorption($notaDina);

                return redirect()->back()->with('success', 'Nota dinas berhasil ditambahkan.');
            });
        } catch (\Exception $e) {
            Log::error('NotaDinas store error: '.$e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Gagal menyimpan nota dinas: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, NotaDinas $notaDina)
    {
        $subKegiatan = SubKegiatan::find($request->sub_kegiatan_id);

        if (!$subKegiatan) {
            return redirect()->back()->with('error', 'Sub kegiatan tidak ditemukan')->withInput();
        }

        $totalSerapanLama = $subKegiatan->notaDinas()->sum('anggaran');
        $sisaPagu = ($subKegiatan->pagu - $totalSerapanLama) + $notaDina->anggaran;

        $validated = $request->validate($this->getValidationRules($sisaPagu, true));

        try {
            return DB::transaction(function () use ($validated, $request, $notaDina) {
                $notaDina->update([
                    'nomor_nota' => $validated['nomor_nota'],
                    'perihal' => $validated['perihal'],
                    'anggaran' => $validated['anggaran'],
                    'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                ]);

                if ($request->hasFile('lampirans')) {
                    // Upload new files
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

                    // delete old files after successful upload
                    foreach ($notaDina->lampirans()->where('created_at', '<', now())->get() as $lampiran) {
                        Storage::disk('public')->delete($lampiran->path);
                        $lampiran->delete();
                    }
                }

                $this->updateBudgetAbsorption($notaDina);

                return redirect()->back()->with('success', 'Nota dinas berhasil diperbarui.');
            });
        } catch (\Exception $e) {
            Log::error('NotaDinas update error: '.$e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Gagal memperbarui nota dinas: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(NotaDinas $notaDina)
    {
        try {
            return DB::transaction(function () use ($notaDina) {
                foreach ($notaDina->lampirans as $lampiran) {
                    Storage::disk('public')->delete($lampiran->path);
                    $lampiran->delete();
                }

                $notaDina->delete();

                $this->updateBudgetAbsorption($notaDina);

                return redirect()->back()->with('success', 'Nota dinas berhasil dihapus.');
            });
        } catch (\Exception $e) {
            Log::error('NotaDinas destroy error: '.$e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Gagal menghapus nota dinas: ' . $e->getMessage());
        }
    }

    protected function updateBudgetAbsorption(NotaDinas $notaDina)
    {
        // Hitung Sub Kegiatan
        $subKegiatan = $notaDina->subKegiatan()->with(['kegiatan.skpd.kabupatens'])->first();
        $totalSerapanSub = $subKegiatan->notaDinas()->sum('anggaran');
        $paguSub = $subKegiatan->pagu > 0 ? $subKegiatan->pagu : 1;

        $subKegiatan->update([
            'total_serapan' => $totalSerapanSub,
            'presentase_serapan' => ($totalSerapanSub / $paguSub) * 100,
        ]);

        // Hitung Kegiatan
        $kegiatan = $subKegiatan->kegiatan;
        $totalSerapanKegiatan = $kegiatan->subKegiatans()->sum('total_serapan');
        $paguKeg = $kegiatan->pagu > 0 ? $kegiatan->pagu : 1;

        $kegiatan->update([
            'total_serapan' => $totalSerapanKegiatan,
            'presentase_serapan' => ($totalSerapanKegiatan / $paguKeg) * 100,
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

    /**
     * Validation rules for store/update Nota Dinas.
     *
     * @param float $sisaPagu
     * @param bool $isUpdate
     * @return array
     */
    private function getValidationRules($sisaPagu, $isUpdate = false)
    {
        $rules = [
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
        ];

        if (!$isUpdate) {
            $rules['sub_kegiatan_id'] = 'required|exists:sub_kegiatans,id';
        }

        return $rules;
    }
}