<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Skpd;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class NotaGutulsController extends Controller
{
    public function notaGuTuLsBySkpd(Skpd $skpd, Request $request)
    {
        $search = $request->input('search');
        $tahun = $request->input('tahun') ?? date('Y');
        $jenis = $request->input('jenis');
        $isBelanjaModal = $request->input('is_belanja_modal');

        // Query utama untuk nota GU/TU/LS
        $notaDinasQuery = NotaDinas::whereIn('jenis', ['GU', 'TU', 'LS'])
            ->whereHas('dikaitkanOleh.subKegiatan.kegiatan', function ($query) use ($skpd) {
                $query->where('skpd_id', $skpd->id);
            })
            ->whereYear('tanggal_pengajuan', $tahun)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_nota', 'like', "%$search%")
                        ->orWhere('perihal', 'like', "%$search%");
                });
            })
            ->when($jenis, function ($query, $jenis) {
                $query->where('jenis', $jenis);
            })
            ->when(isset($isBelanjaModal), function ($query) use ($isBelanjaModal) {
                $query->where('is_belanja_modal', filter_var($isBelanjaModal, FILTER_VALIDATE_BOOLEAN));
            })
            ->with(['dikaitkanOleh.subKegiatan.kegiatan', 'dikaitkanOleh', 'lampirans']);

        $notaDinas = $notaDinasQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Data untuk parent notes (dipakai modal create/edit)
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

        // Filter dropdown
        $tahunOptions = NotaDinas::where('skpd_id', $skpd->id)
            ->selectRaw('YEAR(tanggal_pengajuan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $jenisOptions = NotaDinas::where('skpd_id', $skpd->id)
            ->whereNotNull('jenis')
            ->whereIn('jenis', ['GU', 'TU', 'LS'])
            ->distinct()
            ->pluck('jenis');

        return inertia('NotaDinas/GuTuLsBySkpd', [
            'skpd' => $skpd,
            'notaDinas' => $notaDinas,
            'parentNotes' => $parentNotes,
            'tahun' => $tahun,
            'search' => $search,
            'jenis' => $jenis,
            'is_belanja_modal' => $isBelanjaModal,
            'tahunOptions' => $tahunOptions,
            'jenisOptions' => $jenisOptions,
        ]);
    }
    public function storeGuTuLs(Request $request)
    {
        $this->validateGuTuLs($request);

        // Ambil & lock nota induk
        $parents = NotaDinas::whereIn('id', $request->parent_ids)
            ->lockForUpdate()
            ->with('terkait')
            ->get();

        if ($parents->isEmpty()) {
            return back()
                ->withErrors(['parent_ids' => 'Nota induk tidak ditemukan.'])
                ->withInput();
        }

        // Cek total sisa anggaran
        $totalSisa = $this->calcTotalSisa($parents);
        if ($request->anggaran > $totalSisa) {
            return back()
                ->withErrors([
                    'anggaran' => 'Total anggaran melebihi sisa anggaran. Maksimum: Rp' . number_format($totalSisa, 0, ',', '.')
                ])
                ->withInput();
        }

        // Buat nota dan relasi dalam transaksi
        return DB::transaction(function () use ($request, $parents) {
            $skpdId = $this->resolveSkpd($parents);
            // Buat nota sekaligus isi skpd_id dan sub_kegiatan_id jika tersedia
            $notaDina = NotaDinas::create([
                'nomor_nota' => $request->nomor_nota,
                'perihal' => $request->perihal,
                'anggaran' => $request->anggaran,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jenis' => $request->jenis,
                'skpd_id' => $skpdId,
                'user_id' => auth()->id(),
                'is_belanja_modal' => $request->boolean('is_belanja_modal'),
            ]);

            // Simpan lampiran
            $this->handleAttachments($request, $notaDina);

            // Hubungkan ke nota induk
            $pivot = $this->allocatePivot($parents, $request->anggaran);

            $notaDina->dikaitkanOleh()->attach($pivot);
            // Update budget absorption
            $this->updateBudgetAbsorption($notaDina);

            return redirect()
                ->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
                ->with('success', 'Nota berhasil ditambahkan.');
        });
    }

    public function updateGuTuLs(Request $request, NotaDinas $notaDina)
    {
        $this->validateGuTuLs($request, $notaDina->id);

        $parents = NotaDinas::whereIn('id', $request->parent_ids)
            ->lockForUpdate()
            ->with('terkait')
            ->get();

        if ($parents->isEmpty()) {
            return back()
                ->withErrors(['parent_ids' => 'Nota induk tidak ditemukan.'])
                ->withInput();
        }

        $totalSisa = $this->calcTotalSisa($parents, $notaDina->id);
        if ($request->anggaran > $totalSisa) {
            return back()
                ->withErrors([
                    'anggaran' => "Total anggaran melebihi sisa anggaran. Maksimum: Rp"
                        . number_format($totalSisa, 0, ',', '.')
                ])
                ->withInput();
        }

        return DB::transaction(function () use ($request, $parents, $notaDina) {
            $data = $request->only([
                'nomor_nota',
                'perihal',
                'anggaran',
                'tanggal_pengajuan',
                'jenis',
                'is_belanja_modal',
            ]);

            $data['user_id'] = auth()->id();
            $notaDina->update($data);

            // replace attachments if new ones uploaded
            $this->handleAttachments($request, $notaDina, $isUpdate = true);

            // re-allocate pivot
            $pivot = $this->allocatePivot($parents, $request->anggaran, $notaDina->id);
            $notaDina->dikaitkanOleh()->sync($pivot);
            // Update budget absorption
            $this->updateBudgetAbsorption($notaDina);

            $skpdId = $this->resolveSkpd($parents);
            return redirect()
                ->route('nota-dinas.nota-gutuls', ['skpd' => $skpdId])
                ->with('success', 'Nota berhasil diperbarui.');
        });
    }

    /**
     * Shared validation for both store & update.
     */
    private function validateGuTuLs(Request $request, $ignoreId = null)
    {
        $uniqueNomor = $ignoreId
            ? Rule::unique('nota_dinas', 'nomor_nota')->ignore($ignoreId)
            : 'unique:nota_dinas,nomor_nota';

        $request->validate([
            'nomor_nota' => ['required', 'string', $uniqueNomor],
            'perihal' => 'required|string',
            'anggaran' => 'required|numeric|min:1',
            'tanggal_pengajuan' => 'required|date',
            'jenis' => 'required|in:GU,TU,LS',
            'parent_ids' => 'required|array|min:1',
            'parent_ids.*' => 'exists:nota_dinas,id',
            'lampirans' => 'nullable|array',
            'lampirans.*' => 'file|mimes:pdf|max:3072',
            'is_belanja_modal' => 'boolean',
        ], [
            'parent_ids.required' => 'Nota induk wajib dipilih.',
            'lampirans.*.max' => 'Setiap file lampiran maksimal 3MB.',
            'lampirans.*.mimes' => 'Setiap file lampiran harus berupa file PDF.',
            'lampirans.*.file' => 'Setiap lampiran harus berupa file yang valid.',
        ]);
    }

    /**
     * Calculate the sum of all remaining budgets
     * Optionally exclude one existing child (for update).
     */
    private function calcTotalSisa(Collection $parents, $excludeChildId = null): float
    {
        return $parents->reduce(function ($carry, $parent) use ($excludeChildId) {
            $used = $parent->terkait
                ->when(
                    $excludeChildId,
                    fn($cols) => $cols->filter(fn($c) => $c->id !== $excludeChildId)
                )
                ->sum(fn($c) => $c->pivot->anggaran ?? 0);

            $sisa = max($parent->anggaran - $used, 0);
            return $carry + $sisa;
        }, 0);
    }

    /**
     * Throws if not enough aggregate budget to allocate $need.
     */
    private function allocatePivot(Collection $parents, float $need, $excludeChildId = null): array
    {
        $remaining = $need;
        $pivotData = [];

        foreach ($parents as $parent) {
            $used = $parent->terkait
                ->when(
                    $excludeChildId,
                    fn($cols) => $cols->filter(fn($c) => $c->id !== $excludeChildId)
                )
                ->sum(fn($c) => $c->pivot->anggaran ?? 0);

            $sisa = $parent->anggaran - $used;
            if ($sisa <= 0) {
                continue;
            }

            $take = min($remaining, $sisa);
            $pivotData[$parent->id] = ['anggaran' => $take];
            $remaining -= $take;

            if ($remaining <= 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new \Exception("Sisa anggaran tidak cukup untuk alokasi.");
        }

        return $pivotData;
    }

    /**
     * Save uploaded PDFs. On update, delete old ones first.
     */
    private function handleAttachments(Request $request, NotaDinas $notaDina, bool $isUpdate = false): void
    {
        if (!$request->hasFile('lampirans')) {
            return;
        }

        if ($isUpdate) {
            foreach ($notaDina->lampirans as $lampiran) {
                \Storage::disk('public')->delete($lampiran->path);
                $lampiran->delete();
            }
        }

        foreach ($request->file('lampirans') as $file) {
            $path = $file->store('nota_lampirans', 'public');
            $notaDina->lampirans()->create([
                'nama_file' => $file->getClientOriginalName(),
                'path' => $path,
            ]);
        }
    }

    /**
     * Extract SKPD from the first parent (subKegiatan→kegiatan→skpd_id or fallback).
     */
    private function resolveSkpd(Collection $parents): int
    {
        $first = $parents->first();
        return optional($first->subKegiatan?->kegiatan)->skpd_id
            ?? $first->skpd_id;
    }

    protected function updateBudgetAbsorption(NotaDinas $notaDina)
    {
        // Ambil semua nota induk dari nota terkait ini
        $notaInduks = $notaDina->dikaitkanOleh()->with('subKegiatan.kegiatan.skpd.kabupatens')->get();

        foreach ($notaInduks as $notaInduk) {
            $subKegiatan = $notaInduk->subKegiatan;

            // Hitung total serapan berdasarkan semua child terkait dari nota ini
            $totalSerapanSub = $notaInduk->terkait->sum('pivot.anggaran');
            $paguSub = $subKegiatan->pagu > 0 ? $subKegiatan->pagu : 1;

            $subKegiatan->update([
                'total_serapan' => $totalSerapanSub,
                'presentase_serapan' => ($totalSerapanSub / $paguSub) * 100,
            ]);

            // Update kegiatan
            $kegiatan = $subKegiatan->kegiatan;
            $totalSerapanKegiatan = $kegiatan->subKegiatans()->sum('total_serapan');
            $paguKeg = $kegiatan->pagu > 0 ? $kegiatan->pagu : 1;

            $kegiatan->update([
                'total_serapan' => $totalSerapanKegiatan,
                'presentase_serapan' => ($totalSerapanKegiatan / $paguKeg) * 100,
            ]);

            // Update kabupaten berdasarkan skpd dan tahun
            $skpd = $kegiatan->skpd;
            $tahun = $kegiatan->tahun_anggaran;

            $kabupaten = $skpd->kabupatens()
                ->wherePivot('tahun_anggaran', $tahun)
                ->first();

            if ($kabupaten) {
                $totalSerapanKab = SubKegiatan::whereHas('kegiatan', function ($query) use ($kabupaten, $tahun) {
                    $query->where('tahun_anggaran', $tahun)
                        ->whereHas('skpd', function ($q) use ($kabupaten) {
                            $q->whereHas('kabupatens', fn($k) => $k->where('kabupatens.id', $kabupaten->id));
                        });
                })->sum('total_serapan');

                $kabupaten->update([
                    'total_serapan' => $totalSerapanKab,
                    'presentase_serapan' => $kabupaten->pagu > 0
                        ? ($totalSerapanKab / $kabupaten->pagu) * 100
                        : 0,
                ]);
            }
        }
    }
}
