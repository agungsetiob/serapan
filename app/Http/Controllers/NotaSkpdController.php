<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaSkpdRequest;
use App\Models\NotaDinas;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NotaSkpdController extends Controller
{
    public function index(Request $request)
    {
        $query = Skpd::query();

        if ($search = $request->search) {
            $query->where('nama_skpd', 'like', '%' . $search . '%');
        }

        $skpds = $query->paginate(12);

        return inertia('NotaDinas/IndexSkpd', [
            'skpds' => $skpds,
        ]);
    }

    public function show(Skpd $nota_skpd, Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = NotaDinas::where('skpd_id', $nota_skpd->id)
            ->with(['skpd', 'lampirans', 'dikaitkanOleh', 'terkait.dikaitkanOleh', 'terkait'])
            ->whereNotIn('jenis', ['Pelaksanaan', 'TU', 'LS', 'GU'])
            ->whereYear('tanggal_pengajuan', $tahun)
            ->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_nota', 'like', '%' . $search . '%')
                    ->orWhere('perihal', 'like', '%' . $search . '%');
            });
        }
        if ($jenis = $request->jenis) {
            $query->where('jenis', $jenis);
        }

        $notaDinas = $query->with('terkait')->paginate(10);

        // Ambil tahun-tahun yang tersedia
        $tahunOptions = NotaDinas::where('skpd_id', $nota_skpd->id)
            ->selectRaw('YEAR(tanggal_pengajuan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $jenisOptions = NotaDinas::where('skpd_id', $nota_skpd->id)
            ->whereNotNull('jenis')
            ->select('jenis')
            ->distinct()
            ->whereNotIn('jenis', ['Pelaksanaan', 'TU', 'LS', 'GU'])
            ->pluck('jenis');

        return inertia('NotaDinas/ShowNotaSkpd', [
            'skpd' => $nota_skpd,
            'notaDinas' => $notaDinas,
            'tahunOptions' => $tahunOptions,
            'tahunSelected' => (int) $tahun,
            'jenisOptions' => $jenisOptions,
            'jenisSelected' => $request->jenis,
        ]);
    }
    public function store(NotaSkpdRequest $request)
    {
        return $this->saveNotaDinas($request);
    }

    public function update(NotaSkpdRequest $request, NotaDinas $nota_skpd)
    {
        return $this->saveNotaDinas($request, $nota_skpd);
    }

    protected function saveNotaDinas(NotaSkpdRequest $request, NotaDinas $nota = null)
    {
        $isNew = is_null($nota);
        $data = $request->validated();
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        $amount = isset($data['anggaran']) ? (float) $data['anggaran'] : 0;
        $parentIds = $data['parent_ids'] ?? [];
        $skpdId = $data['skpd_id'];
        $year = date('Y');

        $this->checkParentBudget($parentIds, $amount, $nota?->id);
        $this->checkSKPDBudget($skpdId, $amount, $year, $nota?->id);

        DB::beginTransaction();
        try {
            $nota = $isNew
                ? new NotaDinas($data)
                : tap($nota)->fill($data);

            $nota->anggaran = $amount;
            $nota->tanggal_pengajuan = $data['tanggal_pengajuan'];
            $nota->save();

            if (!empty($parentIds)) {
                $nota->dikaitkanOleh()->syncWithoutDetaching($parentIds);
            }

            if ($request->hasFile('lampirans')) {
                if (!$isNew) {
                    foreach ($nota->lampirans as $lampiran) {
                        \Storage::disk('public')->delete($lampiran->path);
                        $lampiran->delete();
                    }
                }

                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('nota_lampirans', 'public');
                    $nota->lampirans()->create([
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }

            DB::commit();

            $msg = $isNew
                ? 'Nota dinas berhasil dibuat.'
                : 'Nota dinas berhasil diperbarui.';

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saving Nota Dinas: " . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()
                ->with('error', 'Gagal menyimpan nota: ' . $e->getMessage());
        }
    }

    private function checkParentBudget(array $parentIds, float $amount, ?int $currentNotaId = null)
    {
        if (empty($parentIds) || $amount <= 0) {
            return;
        }

        $parents = NotaDinas::with('terkait')->find($parentIds);
        $totalAvailable = 0;

        foreach ($parents as $parent) {
            $used = $parent->terkait
                ->when(
                    $currentNotaId,
                    fn($coll) =>
                    $coll->reject(fn($c) => $c->id === $currentNotaId)
                )
                ->sum('anggaran');

            $totalAvailable += max(0, $parent->anggaran - $used);
        }

        if ($amount > $totalAvailable) {
            $msg = "Anggaran yang diajukan (Rp" . number_format($amount, 0, ',', '.') . ") melebihi sisa kumulatif parent nota. "
                . "Sisa: Rp" . number_format($totalAvailable, 0, ',', '.');

            throw ValidationException::withMessages([
                'anggaran' => $msg
            ]);
        }
    }

    private function checkSKPDBudget(int $skpdId, float $amount, int $year, ?int $currentNotaId = null)
    {
        if ($amount <= 0) {
            return;
        }

        $skpd = SKPD::with(['kegiatans' => fn($q) => $q->where('tahun_anggaran', $year)])
            ->findOrFail($skpdId);
        $totalPagu = $skpd->kegiatans->sum('pagu');
        $existing = NotaDinas::where('skpd_id', $skpdId)
            ->when($currentNotaId, fn($q) => $q->where('id', '!=', $currentNotaId))
            ->whereYear('tanggal_pengajuan', $year)
            ->sum('anggaran');
        $remaining = $totalPagu - $existing;

        if ($amount > $remaining) {
            $msg = "Anggaran yang diajukan (Rp" . number_format($amount, 0, ',', '.') . ") melebihi sisa anggaran SKPD. "
                . "Sisa: Rp" . number_format(max(0, $remaining), 0, ',', '.');

            throw ValidationException::withMessages([
                'anggaran' => $msg
            ]);
        }
    }

    public function destroy(NotaDinas $nota_skpd)
    {
        DB::beginTransaction();

        try {
            foreach ($nota_skpd->lampirans as $lampiran) {
                Storage::disk('public')->delete($lampiran->path);
                $lampiran->delete();
            }

            $nota_skpd->terkait()->detach();
            $nota_skpd->dikaitkanOleh()->detach();
            $nota_skpd->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Nota dinas berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus nota dinas: ' . $e->getMessage());
        }
    }
}
