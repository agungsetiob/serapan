<?php

namespace App\Helpers;

class SerapanHelper
{
    public static function hitungSubKegiatan($sub)
    {
        $totalAnggaran = 0;
        $totalSerapan = 0;

        foreach ($sub->notaDinas as $nota) {
            $totalAnggaran += $nota->anggaran;
            $totalSerapan += $nota->terkait->sum('pivot.anggaran');
        }

        $sub->total_anggaran = $totalAnggaran;
        $sub->total_serapan = $totalSerapan;
        $sub->presentase_serapan = $totalAnggaran > 0
            ? round(($totalSerapan / $totalAnggaran) * 100, 2)
            : 0;
    }

    public static function hitungKegiatan($kegiatan)
    {
        $totalSerapan = 0;

        foreach ($kegiatan->subKegiatans as $sub) {
            self::hitungSubKegiatan($sub);
            $totalSerapan += $sub->total_serapan;
        }

        $kegiatan->total_serapan = $totalSerapan;
    }

    public static function hitungProgram($program)
    {
        $totalSerapan = 0;
        $totalPagu = $program->kegiatans->sum('pagu');

        foreach ($program->kegiatans as $kegiatan) {
            self::hitungKegiatan($kegiatan);
            $totalSerapan += $kegiatan->total_serapan;
        }

        $program->pagu = $totalPagu;
        $program->total_serapan = $totalSerapan;
        $program->presentase_serapan = $totalPagu > 0
            ? round(($totalSerapan / $totalPagu) * 100, 2)
            : 0;
    }
}
