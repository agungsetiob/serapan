<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\NotaDinas;
use App\Models\Skpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\Inertia;

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

        return inertia('Skpds/Index', [
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

    // public function show(SKPD $skpd)
    // {
    //     $tahun = date('Y');

    //     $skpd->load([
    //         'programs' => function ($query) use ($tahun) {
    //             $query->where('tahun_anggaran', $tahun)
    //                 ->with([
    //                     'kegiatans' => function ($q) use ($tahun) {
    //                         $q->where('tahun_anggaran', $tahun)
    //                             ->with([
    //                                 'subKegiatans' => function ($sq) use ($tahun) {
    //                                     $sq->where('tahun_anggaran', $tahun)
    //                                         ->with(['notaDinas.subKegiatan', 'notaDinas.lampirans']);
    //                                 }
    //                             ]);
    //                     }
    //                 ]);
    //         }
    //     ]);
    //     foreach ($skpd->programs as $program) {
    //         $totalPagu = 0;
    //         $totalSerapan = 0;

    //         foreach ($program->kegiatans as $kegiatan) {
    //             $totalPagu += $kegiatan->pagu;
    //             $totalSerapan += $kegiatan->total_serapan;
    //         }

    //         $program->pagu = $totalPagu;
    //         $program->total_serapan = $totalSerapan;
    //         $program->presentase_serapan = $totalPagu > 0
    //             ? round(($totalSerapan / $totalPagu) * 100, 2)
    //             : 0;
    //     }

    //     $totalPaguSkpd = 0;
    //     $totalSerapanSkpd = 0;

    //     foreach ($skpd->programs as $program) {
    //         foreach ($program->kegiatans as $kegiatan) {
    //             $totalPaguSkpd += $kegiatan->pagu;
    //             $totalSerapanSkpd += $kegiatan->total_serapan;
    //         }
    //     }

    //     $persentaseSerapanSkpd = $totalPaguSkpd > 0
    //         ? round(($totalSerapanSkpd / $totalPaguSkpd) * 100, 2)
    //         : 0;
    //     foreach ($skpd->programs as $program) {
    //         foreach ($program->kegiatans as $kegiatan) {
    //             foreach ($kegiatan->subKegiatans as $sub) {
    //                 foreach ($sub->notaDinas as $nota) {
    //                     $nota->dipakai_dari_induk = $nota->terkait->sum('pivot.anggaran');
    //                 }
    //             }
    //         }
    //     }
    //     return Inertia::render('Skpds/SkpdDetail', [
    //         'skpd' => $skpd,
    //         'tahunSelected' => (int) $tahun,
    //         'rekap' => [
    //             'totalPagu' => $totalPaguSkpd,
    //             'totalSerapan' => $totalSerapanSkpd,
    //             'persentaseSerapan' => $persentaseSerapanSkpd,
    //         ],
    //     ]);
    // }
    public function show(Request $request, Skpd $skpd)
    {
        $tahun = date('Y');
        $search = $request->search;

        $skpd->load([
            'programs' => function ($query) use ($tahun, $search) {
                $query->where('tahun_anggaran', $tahun)
                    ->with([
                        'kegiatans' => function ($q) use ($tahun, $search) {
                            $q->where('tahun_anggaran', $tahun)
                                ->with([
                                    'subKegiatans' => function ($sq) use ($tahun, $search) {
                                        $sq->where('tahun_anggaran', $tahun)
                                            ->when($search, function ($subQuery) use ($search) {
                                                $subQuery->where('nama', 'like', '%' . $search . '%');
                                            })
                                            ->with(['notaDinas.subKegiatan', 'notaDinas.lampirans']);
                                    }
                                ]);
                        }
                    ]);
            }
        ]);

        foreach ($skpd->programs as $program) {
            $totalPagu = 0;
            $totalSerapan = 0;

            foreach ($program->kegiatans as $kegiatan) {
                $totalPagu += $kegiatan->pagu;
                $totalSerapan += $kegiatan->total_serapan;
            }

            $program->pagu = $totalPagu;
            $program->total_serapan = $totalSerapan;
            $program->presentase_serapan = $totalPagu > 0
                ? round(($totalSerapan / $totalPagu) * 100, 2)
                : 0;
        }

        $totalPaguSkpd = 0;
        $totalSerapanSkpd = 0;

        foreach ($skpd->programs as $program) {
            foreach ($program->kegiatans as $kegiatan) {
                $totalPaguSkpd += $kegiatan->pagu;
                $totalSerapanSkpd += $kegiatan->total_serapan;
            }
        }

        $persentaseSerapanSkpd = $totalPaguSkpd > 0
            ? round(($totalSerapanSkpd / $totalPaguSkpd) * 100, 2)
            : 0;

        foreach ($skpd->programs as $program) {
            foreach ($program->kegiatans as $kegiatan) {
                foreach ($kegiatan->subKegiatans as $sub) {
                    foreach ($sub->notaDinas as $nota) {
                        $nota->dipakai_dari_induk = $nota->terkait->sum('pivot.anggaran');
                    }
                }
            }
        }

        return Inertia::render('Skpds/SkpdDetail', [
            'skpd' => $skpd,
            'tahunSelected' => (int) $tahun,
            'search' => $search,
            'rekap' => [
                'totalPagu' => $totalPaguSkpd,
                'totalSerapan' => $totalSerapanSkpd,
                'persentaseSerapan' => $persentaseSerapanSkpd,
            ],
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

    public function showByYear(SKPD $skpd, $tahun = null)
    {
        $tahun = $tahun ?: date('Y');

        $skpd->load([
            'programs' => function ($query) use ($tahun) {
                $query->where('tahun_anggaran', $tahun)
                    ->with([
                        'kegiatans' => function ($q) use ($tahun) {
                            $q->where('tahun_anggaran', $tahun)
                                ->with([
                                    'subKegiatans' => function ($sq) use ($tahun) {
                                        $sq->where('tahun_anggaran', $tahun)
                                            ->with(['notaDinas.subKegiatan']);
                                    }
                                ]);
                        }
                    ]);
            }
        ]);

        $totalPaguSkpd = 0;
        $totalSerapanSkpd = 0;

        foreach ($skpd->programs as $program) {
            foreach ($program->kegiatans as $kegiatan) {
                $totalPaguSkpd += $kegiatan->pagu;
                $totalSerapanSkpd += $kegiatan->total_serapan;
            }
        }

        $persentaseSerapanSkpd = $totalPaguSkpd > 0
            ? round(($totalSerapanSkpd / $totalPaguSkpd) * 100, 2)
            : 0;

        // Ambil tahun kegiatan yang tersedia
        $tahunTersedia = Kegiatan::where('skpd_id', $skpd->id)
            ->distinct()
            ->pluck('tahun_anggaran')
            ->sortDesc()
            ->values();

        return Inertia::render('Skpds/ShowByYear', [
            'skpd' => $skpd,
            'tahunSelected' => (int) $tahun,
            'tahunTersedia' => $tahunTersedia,
            'rekap' => [
                'totalPagu' => $totalPaguSkpd,
                'totalSerapan' => $totalSerapanSkpd,
                'persentaseSerapan' => $persentaseSerapanSkpd,
            ],
        ]);
    }
    public function showRekap(Skpd $skpd, Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));

        $rekap = NotaDinas::select('jenis')
            ->selectRaw('COUNT(*) as jumlah')
            ->where('skpd_id', $skpd->id)
            ->whereYear('tanggal_pengajuan', $tahun)
            ->groupBy('jenis')
            ->orderBy('jenis')
            ->get();

        $tahunTersedia = NotaDinas::where('skpd_id', $skpd->id)
            ->selectRaw('YEAR(tanggal_pengajuan) as tahun')
            ->distinct()
            ->pluck('tahun')
            ->sortDesc()
            ->values();

        return Inertia::render('Skpds/RekapSkpd', [
            'skpd' => $skpd,
            'tahunDipilih' => (int) $tahun,
            'tahunTersedia' => $tahunTersedia,
            'rekap' => $rekap,
        ]);
    }
    public function apiRekapNotaPerJenis(Skpd $skpd, Request $request)
    {
        $tahun = $request->query('tahun', date('Y'));

        $rekap = NotaDinas::select('jenis')
            ->selectRaw('COUNT(*) as jumlah')
            ->where('skpd_id', $skpd->id)
            ->whereYear('tanggal_pengajuan', $tahun)
            ->groupBy('jenis')
            ->orderBy('jenis')
            ->get();

        return response()->json([
            'tahun' => (int) $tahun,
            'skpd_id' => $skpd->id,
            'rekap' => $rekap,
        ]);
    }
    public function apiTrenNotaPerBulan(Skpd $skpd, Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = DB::table('nota_dinas')
            ->selectRaw('MONTH(tanggal_pengajuan) as bulan, COUNT(*) as jumlah')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->where('skpd_id', $skpd->id)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // bulan 1–12 muncul walaupun kosong
        $tren = collect(range(1, 12))->map(function ($bulan) use ($data) {
            $item = $data->firstWhere('bulan', $bulan);
            return [
                'bulan' => $bulan,
                'jumlah' => $item->jumlah ?? 0,
            ];
        });

        return response()->json(['data' => $tren]);
    }
    public function apiDistribusiSubKegiatan(Skpd $skpd, Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = DB::table('sub_kegiatans as sub')
            ->join('kegiatans as k', 'sub.kegiatan_id', '=', 'k.id')
            ->where('k.skpd_id', $skpd->id)
            ->where('sub.tahun_anggaran', $tahun)
            ->select('sub.nama', 'sub.pagu', 'k.nama as kegiatan')
            ->orderByDesc('sub.pagu')
            ->get();

        return response()->json(['data' => $data]);
    }
}
