<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            ->with(['skpd', 'lampirans', 'terkait', 'dikaitkanOleh'])
            ->whereYear('tanggal_pengajuan', $tahun)
            ->latest();
        
        if ($search = $request->search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_nota', 'like', '%'.$search.'%')
                  ->orWhere('perihal', 'like', '%'.$search.'%');
            });
        }
        
        $notaDinas = $query->paginate(10);
        
        // Ambil tahun-tahun yang tersedia
        $tahunOptions = NotaDinas::where('skpd_id', $nota_skpd->id)
            ->selectRaw('YEAR(tanggal_pengajuan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
        
        return inertia('NotaDinas/ShowNotaSkpd', [
            'skpd' => $nota_skpd,
            'notaDinas' => $notaDinas,
            'tahunOptions' => $tahunOptions,
            'tahunSelected' => (int)$tahun,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nomor_nota' => 'required|string|max:100|unique:nota_dinas,nomor_nota',
            'perihal' => 'required|string|max:255',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'jenis' => 'required|in:Pelaksanaan,Perbup,Lain-lain,GU,TU,LS',
            'skpd_id' => 'required|exists:skpds,id',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:nota_dinas,id',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf',
        ], [
            'nomor_nota.unique' => 'Nomor Nota ini sudah ada.',
            'lampirans.*.max' => 'Ukuran setiap file lampiran maksimal 3MB.',
            'lampirans.*.mimes' => 'Setiap file lampiran harus berupa file PDF.',
            'skpd_id.required' => 'SKPD harus dipilih.',
            'skpd_id.exists' => 'SKPD tidak valid.',
        ]);

        $anggaranToCreate = (float) $validatedData['anggaran'];
        $parentIds = $validatedData['parent_ids'] ?? [];

        if (!empty($parentIds)) {
            $parentNotas = NotaDinas::whereIn('id', $parentIds)->get();

            $totalAvailableParentBudget = 0;
            foreach ($parentNotas as $parent) {
                $parent->load('terkait');
                $budgetUsedByChildren = $parent->terkait->sum('anggaran');
                $remainingBudgetOfParent = $parent->anggaran - $budgetUsedByChildren;

                $totalAvailableParentBudget += max(0, $remainingBudgetOfParent);
            }

            if ($anggaranToCreate > $totalAvailableParentBudget) {
                return back()->withInput()->withErrors([
                    'anggaran' => "Anggaran yang diajukan (Rp" . number_format($anggaranToCreate, 0, ',', '.') . ") melebihi sisa anggaran dari nota terkait. Sisa: Rp" . number_format($totalAvailableParentBudget, 0, ',', '.')
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $notaDina = NotaDinas::create([
                'nomor_nota' => $validatedData['nomor_nota'],
                'perihal' => $validatedData['perihal'],
                'anggaran' => $anggaranToCreate,
                'tanggal_pengajuan' => $validatedData['tanggal_pengajuan'],
                'jenis' => $validatedData['jenis'],
                'skpd_id' => $validatedData['skpd_id'],
            ]);

            if (!empty($parentIds)) {
                $notaDina->dikaitkanOleh()->attach($parentIds);
            }

            if ($request->hasFile('lampirans')) {
                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('nota_lampirans', 'public');
                    $notaDina->lampirans()->create([
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Nota dinas berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error storing Nota Dinas: " . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Gagal menambahkan nota dinas: ' . $e->getMessage());
        }
    }

    public function update(Request $request, NotaDinas $nota_skpd)
    {
        $validatedData = $request->validate([
            'nomor_nota' => [
                'required',
                'string',
                'max:100',
                Rule::unique('nota_dinas')->ignore($nota_skpd->id)
            ],
            'perihal' => 'required|string|max:255',
            'anggaran' => 'nullable|numeric|min:0',
            'tanggal_pengajuan' => 'required|date',
            'jenis' => 'required|in:Pelaksanaan,Perbup,Lain-lain,GU,TU,LS',
            'skpd_id' => 'required|exists:skpds,id',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:nota_dinas,id',
            'lampirans.*' => 'nullable|file|max:3072|mimes:pdf',
        ], [
            'nomor_nota.unique' => 'Nomor Nota ini sudah ada.',
            'lampirans.*.max' => 'Ukuran setiap file lampiran maksimal 3MB.',
            'lampirans.*.mimes' => 'Setiap file lampiran harus berupa file PDF.',
            'skpd_id.required' => 'SKPD harus dipilih.',
            'skpd_id.exists' => 'SKPD tidak valid.',
        ]);

        $newAnggaran = (float) $validatedData['anggaran'];
        $newParentIds = $validatedData['parent_ids'] ?? [];

        DB::beginTransaction();

        try {
            if (!empty($newParentIds)) {
                $parentNotas = NotaDinas::whereIn('id', $newParentIds)->get();

                $totalAvailableParentBudget = 0;
                foreach ($parentNotas as $parent) {
                    $parent->load('terkait');

                    $budgetUsedByChildrenExcludingCurrent = $parent->terkait->sum(function ($child) use ($nota_skpd) {
                        return ($child->id === $nota_skpd->id) ? 0 : $child->anggaran;
                    });

                    $remainingBudgetOfParent = $parent->anggaran - $budgetUsedByChildrenExcludingCurrent;
                    $totalAvailableParentBudget += max(0, $remainingBudgetOfParent);
                }

                if ($newAnggaran > $totalAvailableParentBudget) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'anggaran' => "Anggaran yang diajukan (Rp" . number_format($newAnggaran, 0, ',', '.') . ") melebihi sisa anggaran kumulatif dari nota-nota terkait yang dipilih. Maksimum: Rp" . number_format($totalAvailableParentBudget, 0, ',', '.')
                    ]);
                }
            }

            $nota_skpd->update([
                'nomor_nota' => $validatedData['nomor_nota'],
                'perihal' => $validatedData['perihal'],
                'anggaran' => $newAnggaran,
                'tanggal_pengajuan' => $validatedData['tanggal_pengajuan'],
                'jenis' => $validatedData['jenis'],
                'skpd_id' => $validatedData['skpd_id'],
            ]);

            $existingParentIds = $nota_skpd->dikaitkanOleh()->pluck('nota_dinas.id')->toArray();
            $mergedParentIds = array_unique(array_merge($existingParentIds, $newParentIds));

            $nota_skpd->dikaitkanOleh()->sync($mergedParentIds);


            if ($request->hasFile('lampirans')) {
                foreach ($request->file('lampirans') as $file) {
                    $path = $file->store('nota_lampirans', 'public');
                    $nota_skpd->lampirans()->create([
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Nota dinas berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating Nota Dinas: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal memperbarui nota dinas: ' . $e->getMessage());
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
            return redirect()->back()->with('error', 'Gagal menghapus nota dinas: ' . $e->getMessage());
        }
    }
}