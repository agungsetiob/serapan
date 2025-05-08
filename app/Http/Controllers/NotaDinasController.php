<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\NotaLampiran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotaDinasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $query = NotaDinas::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_nota', 'like', "%$search%")
                ->orWhere('perihal', 'like', "%$search%");
            });
        }

        switch ($user->role) {
            case 'admin':
                // Admin can see all data
                break;
            default:
                return abort(403, 'Akses tidak diizinkan');
        }

        $notas = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('NotaDinas/Index', [
            'notas' => $notas,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'anggaran' => 'nullable|numeric',
            'tanggal_pengajuan' => 'required|date',
            //'lampiran.*' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        NotaDinas::create([
            'skpd_id' => auth()->user()->skpd_id,
            'nomor_nota' => $validated['nomor_nota'],
            'perihal' => $validated['perihal'],
            'anggaran' => $validated['anggaran'],
            'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
            'status' => 'draft',
            'tahap_saat_ini' => 'skpd',
            'asisten_id' => auth()->user()->skpd->asisten_id ?? null,
        ]);

        return redirect()->route('nota-dinas.index')
                         ->with('success', 'Nota berhasil dibuat.');
    }

    public function update(Request $request, NotaDinas $notaDina)
    {
        if (!in_array($notaDina->status, ['draft', 'dikembalikan'])) {
            return redirect()->route('nota-dinas.index')
                             ->with('error', 'Nota hanya bisa diperbarui jika berstatus draft atau dikembalikan.');
        }
    
        $validated = $request->validate([
            'nomor_nota' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'anggaran' => 'nullable|numeric',
            'tanggal_pengajuan' => 'required|date',
            //'lampiran.*' => 'nullable|file|mimes:pdf|max:2048',
        ]);
    
        DB::transaction(function () use ($notaDina, $validated, $request) {
            $notaDina->update([
                'nomor_nota' => $validated['nomor_nota'],
                'perihal' => $validated['perihal'],
                'anggaran' => $validated['anggaran'],
                'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
                'asisten_id' => auth()->user()->skpd->asisten_id ?? null,
            ]);
    
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $path = $file->store('lampiran_nota', 'public');
    
                    NotaLampiran::create([
                        'nota_dinas_id' => $notaDina->id,
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }
        });
        return redirect()->route('nota-dinas.index')
                         ->with('success', 'Nota berhasil diperbarui.');
    }    

    public function destroy(NotaDinas $notaDina)
    {
        if (!in_array($notaDina->status, ['draft', 'dikembalikan'])) {
            return redirect()->route('nota-dinas.index')
                             ->with('error', 'Nota hanya bisa dihapus jika berstatus draft atau dikembalikan.');
        }
        foreach ($notaDina->lampirans as $lampiran) {
            Storage::delete('storage/' . $lampiran->path);
        }
        $notaDina->delete();

        return redirect()->route('nota-dinas.index')
                         ->with('success', 'Nota berhasil dihapus');
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
