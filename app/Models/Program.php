<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'skpd_id',
        'nama',
        'tahun_anggaran',
    ];

    /**
     *  SKPD yang memiliki program ini.
     */
    public function skpd()
    {
        return $this->belongsTo(Skpd::class);
    }

    /**
     *  semua kegiatan untuk program ini.
     */
    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function getPaguAttribute()
    {
        return $this->kegiatans->sum('pagu');
    }

    /**
     *  total serapan untuk program ini (agregasi dari kegiatan).
     */
    public function getTotalSerapanAttribute()
    {
        return $this->kegiatans->sum('total_serapan');
    }

    /**
     *  presentase serapan untuk program ini.
     */
    public function getPresentaseSerapanAttribute()
    {
        $pagu = $this->getPaguAttribute();
        if ($pagu > 0) {
            return ($this->getTotalSerapanAttribute() / $pagu) * 100;
        }
        return 0;
    }
}
