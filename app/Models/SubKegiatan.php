<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKegiatan extends Model
{
    use HasFactory;

    protected $fillable = ['kegiatan_id', 'nama', 'pagu', 'tahun_anggaran', 'total_serapan', 'presentase_serapan'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function notaDinas()
    {
        return $this->hasMany(NotaDinas::class);
    }
}

