<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\User;
use App\Models\Skpd;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $role = auth()->user()->role;
        $totalUsers = User::count();
        $totalSkpds = Skpd::count();
        $notaDinas = NotaDinas::count();
        $notaSelesai = NotaDinas::count();
        //sleep(1);
        return match ($role) {
            'admin' => Inertia::render('Dashboard/Admin', [
                'totalUsers' => $totalUsers,
                'totalSkpds' => $totalSkpds,
                'notaDinas' => $notaDinas,
                'notaSelesai' => $notaSelesai,
            ]),
            default => abort(403),
        };
    }
    public function getNotaPerYear()
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

}
