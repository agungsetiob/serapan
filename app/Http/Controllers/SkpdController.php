<?php

namespace App\Http\Controllers;

use App\Models\Skpd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkpdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Skpd::query();
    
        if ($search = $request->search) {
            $query->where('nama_skpd', 'like', '%' . $search . '%');
        }
    
        $skpds = $query->paginate(10);
    
        return Inertia::render('Skpds/Index', [
            'skpds' => $skpds,
        ]);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_skpd' => 'required|string|max:255',
        ]);

        Skpd::create([
            'nama_skpd' => $validated['nama_skpd'],
            'status' => true,
        ]);

        return back()->with('success', 'SKPD berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skpd $skpd)
    {
        $validated = $request->validate([
            'nama_skpd' => 'required|string|max:255',
        ]);

        $skpd->update([
            'nama_skpd' => $validated['nama_skpd'],
        ]);

        return back()->with('success', 'SKPD berhasil diupdate.');
    }

    public function show(SKPD $skpd)
    {
        $skpd->load(['kegiatans.subKegiatans']);
        return Inertia::render('Skpds/SkpdDetail', [
            'skpd' => $skpd
        ]);
    }

    /**
     * Toggle the status of a SKPD.
     */
    public function toggleStatus(Skpd $skpd)
    {
        $skpd->update(['status' => !$skpd->status]);

        return back()->with('success', 'Status SKPD berhasil diubah.');
    }
}
