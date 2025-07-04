<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKegiatan extends Model
{
    use HasFactory;

    protected $fillable = ['kegiatan_id', 'kode_rekening', 'nama', 'pagu', 'tahun_anggaran', 'total_serapan', 'presentase_serapan'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function notaDinas()
    {
        return $this->hasMany(NotaDinas::class);
    }

    protected $casts = [
        'pagu' => 'float',
        'total_serapan' => 'float',
        'presentase_serapan' => 'float',
        'tahun_anggaran' => 'integer',
    ];
}

