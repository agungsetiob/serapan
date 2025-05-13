<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Skpd;
use App\Models\Kabupaten;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $role = auth()->user()->role;
        $tahunSekarang = date('Y');
        $totalSkpds = Skpd::count();
        $notaDinas = NotaDinas::whereYear('tanggal_pengajuan', $tahunSekarang)->count();

        $kabupaten = Kabupaten::where('tahun_anggaran', $tahunSekarang)->first();

        return match ($role) {
            'admin' => inertia('Dashboard/Admin', [
                'totalSkpds' => $totalSkpds,
                'notaDinas' => $notaDinas,
                'presentaseSerapan' => (float)$kabupaten?->presentase_serapan ?? 0,
                'totalSerapan' => (float)$kabupaten?->total_serapan ?? 0,
                'kabupaten' => $kabupaten 
            ]),
            default => abort(403),
        };
    }  
    public function getNotaPerYear():JsonResponse
    {
        $startYear = Carbon::now()->subYears(4)->year;
    
        $data = DB::table('nota_dinas')
            ->selectRaw('YEAR(tanggal_pengajuan) as year, COUNT(*) as total')
            ->whereYear('tanggal_pengajuan', '>=', $startYear)
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();
    
        return response()->json($data);
    }
    public function topSkpdSerapan(): JsonResponse
    {
        $skpds = Skpd::with(['kegiatans' => function($query) {
                $query->select('skpd_id', 'total_serapan', 'pagu');
            }])
            ->where('status', true)
            ->get()
            ->map(function($skpd) {
                $totalSerapan = $skpd->kegiatans->sum('total_serapan');
                $totalPagu = $skpd->kegiatans->sum('pagu');
                
                return [
                    'id' => $skpd->id,
                    'nama_skpd' => $skpd->nama_skpd,
                    'total_serapan' => $totalSerapan,
                    'pagu' => $totalPagu,
                    'presentase_serapan' => $totalPagu > 0 ? round(($totalSerapan / $totalPagu) * 100, 2) : 0
                ];
            })
            ->sortByDesc('presentase_serapan')
            ->take(10)
            ->values()
            ->all();

        return response()->json($skpds);
    }
    public function getKabupatenSerapanData(): JsonResponse
    {
        $data = Kabupaten::select('tahun_anggaran', 'total_serapan', 'presentase_serapan')
            ->orderBy('tahun_anggaran')
            ->get();

        return response()->json([
            'labels' => $data->pluck('tahun_anggaran'),
            'total_serapan' => $data->pluck('total_serapan'),
            'presentase_serapan' => $data->pluck('presentase_serapan')
        ]);
    }

}
